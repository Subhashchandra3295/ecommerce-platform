"use client";

import { use, useEffect, useState } from "react";
import { RequireAuth } from "@/components/RequireAuth";
import { useCart } from "@/context/CartContext";
import { api, ApiError } from "@/lib/api";
import { formatCents } from "@/lib/money";
import type { Order } from "@/lib/types";

function OrderDetail({ id, success }: { id: string; success: boolean }) {
  const { refresh } = useCart();
  const [order, setOrder] = useState<Order | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    api
      .getOrder(id)
      .then(setOrder)
      .catch((err) => setError(err instanceof ApiError ? err.message : "Order not found"));
  }, [id]);

  useEffect(() => {
    if (success) {
      // Checkout clears the cart server-side via a queued job; refresh local state to match.
      refresh().catch(() => {});
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [success]);

  if (error) {
    return <p className="text-sm text-red-600">{error}</p>;
  }

  if (!order) {
    return <p className="text-sm text-black/60 dark:text-white/60">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-6">
      {success && (
        <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
          Thanks! Your order has been placed.
        </div>
      )}

      <div>
        <h1 className="text-2xl font-semibold">Order #{order.id}</h1>
        <p className="mt-1 text-sm text-black/60 dark:text-white/60">Status: {order.status}</p>
      </div>

      <ul className="flex flex-col divide-y divide-black/10 dark:divide-white/10">
        {order.items.map((item) => (
          <li key={item.id} className="flex items-center justify-between py-3">
            <span>
              {item.product_name} &times; {item.quantity}
            </span>
            <span>{formatCents(item.unit_price_cents * item.quantity)}</span>
          </li>
        ))}
      </ul>

      <div className="flex items-center justify-between border-t border-black/10 pt-4 dark:border-white/10">
        <p className="font-medium">Total</p>
        <p className="text-lg font-semibold">{formatCents(order.total_cents)}</p>
      </div>
    </div>
  );
}

export default function OrderPage({
  params,
  searchParams,
}: PageProps<"/orders/[id]">) {
  const { id } = use(params);
  const { success } = use(searchParams);
  return (
    <RequireAuth>
      <OrderDetail id={id} success={success === "true"} />
    </RequireAuth>
  );
}
