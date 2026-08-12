<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Jobs\ProcessPaidOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function signedRequest(array $payloadArray): array
    {
        $payload = json_encode($payloadArray);
        $secret = config('services.stripe.webhook_secret');
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
        $header = "t={$timestamp},v1={$signature}";

        return [$payload, $header];
    }

    public function test_it_rejects_a_request_with_an_invalid_signature(): void
    {
        [$payload] = $this->signedRequest(['type' => 'checkout.session.completed']);

        $response = $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => 't=1,v1=invalid',
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(400);
    }

    public function test_a_valid_checkout_session_completed_event_marks_the_order_paid_and_queues_processing(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        [$payload, $signature] = $this->signedRequest([
            'id' => 'evt_test_1',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_intent' => 'pi_test_123',
                ],
            ],
        ]);

        $response = $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertOk();

        $order->refresh();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame('pi_test_123', $order->stripe_payment_intent_id);

        Queue::assertPushed(ProcessPaidOrder::class, fn ($job) => $job->orderId === $order->id);
    }

    public function test_it_ignores_an_event_for_an_order_that_is_already_paid(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Paid,
            'stripe_checkout_session_id' => 'cs_test_already_paid',
        ]);

        [$payload, $signature] = $this->signedRequest([
            'id' => 'evt_test_2',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_already_paid',
                    'payment_intent' => 'pi_test_999',
                ],
            ],
        ]);

        $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        Queue::assertNotPushed(ProcessPaidOrder::class);
    }
}
