"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { RequireAuth } from "@/components/RequireAuth";
import { api, ApiError } from "@/lib/api";
import { formatCents } from "@/lib/money";
import type { Order } from "@/lib/types";

function OrdersContent() {
  const [orders, setOrders] = useState<Order[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    api
      .listOrders()
      .then(setOrders)
      .catch((err) => setError(err instanceof ApiError ? err.message : "Failed to load orders"));
  }, []);

  return (
    <div className="flex flex-col gap-6">
      <h1 className="text-2xl font-semibold">Your Orders</h1>

      {error && <p className="text-sm text-red-600">{error}</p>}

      {orders === null ? (
        <p className="text-sm text-black/60 dark:text-white/60">Loading...</p>
      ) : orders.length === 0 ? (
        <p className="text-sm text-black/60 dark:text-white/60">No orders yet.</p>
      ) : (
        <ul className="flex flex-col divide-y divide-black/10 dark:divide-white/10">
          {orders.map((order) => (
            <li key={order.id} className="py-4">
              <Link href={`/orders/${order.id}`} className="flex items-center justify-between hover:underline">
                <span>
                  Order #{order.id} &middot;{" "}
                  <span className="text-black/60 dark:text-white/60">{order.status}</span>
                </span>
                <span>{formatCents(order.total_cents)}</span>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

export default function OrdersPage() {
  return (
    <RequireAuth>
      <OrdersContent />
    </RequireAuth>
  );
}
