<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaidOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::with('items', 'user')->findOrFail($this->orderId);

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $item->product?->decrement('stock', $item->quantity);
            }

            $order->user->cart?->items()->delete();
        });

        // In production this would call a transactional email provider (e.g. Postmark, SES).
        Log::info("[order-confirmation] Order #{$order->id} confirmed for {$order->user->email}, total \${$order->formattedTotal()}");
    }
}
