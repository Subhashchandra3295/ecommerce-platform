"use client";

import { useState } from "react";
import { RequireAuth } from "@/components/RequireAuth";
import { useCart } from "@/context/CartContext";
import { api, ApiError } from "@/lib/api";
import { formatCents } from "@/lib/money";

function CartContent() {
  const { cart, updateItem, removeItem, refresh } = useCart();
  const [error, setError] = useState<string | null>(null);
  const [checkingOut, setCheckingOut] = useState(false);

  const total = cart?.items.reduce(
    (sum, item) => sum + item.quantity * item.product.price_cents,
    0,
  ) ?? 0;

  async function handleCheckout() {
    setCheckingOut(true);
    setError(null);
    try {
      const { checkout_url } = await api.checkout();
      window.location.href = checkout_url;
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Checkout failed");
      setCheckingOut(false);
    }
  }

  if (!cart) {
    return <p className="text-sm text-black/60 dark:text-white/60">Loading cart...</p>;
  }

  return (
    <div className="flex flex-col gap-6">
      <h1 className="text-2xl font-semibold">Your Cart</h1>

      {error && <p className="text-sm text-red-600">{error}</p>}

      {cart.items.length === 0 ? (
        <p className="text-sm text-black/60 dark:text-white/60">Your cart is empty.</p>
      ) : (
        <>
          <ul className="flex flex-col divide-y divide-black/10 dark:divide-white/10">
            {cart.items.map((item) => (
              <li key={item.id} className="flex items-center justify-between gap-4 py-4">
                <div>
                  <p className="font-medium">{item.product.name}</p>
                  <p className="text-sm text-black/60 dark:text-white/60">
                    {formatCents(item.product.price_cents)} each
                  </p>
                </div>
                <div className="flex items-center gap-3">
                  <input
                    type="number"
                    min={1}
                    value={item.quantity}
                    onChange={(e) => {
                      const qty = Number(e.target.value);
                      if (qty >= 1) updateItem(item.id, qty).then(refresh).catch(() => {});
                    }}
                    className="w-16 rounded-md border border-black/15 px-2 py-1 text-sm dark:border-white/20"
                  />
                  <p className="w-20 text-right text-sm">
                    {formatCents(item.quantity * item.product.price_cents)}
                  </p>
                  <button
                    onClick={() => removeItem(item.id)}
                    className="text-sm text-red-600 hover:underline"
                  >
                    Remove
                  </button>
                </div>
              </li>
            ))}
          </ul>

          <div className="flex items-center justify-between border-t border-black/10 pt-4 dark:border-white/10">
            <p className="font-medium">Total</p>
            <p className="text-lg font-semibold">{formatCents(total)}</p>
          </div>

          <button
            onClick={handleCheckout}
            disabled={checkingOut}
            className="w-fit rounded-md bg-black px-6 py-3 text-sm font-medium text-white disabled:opacity-50 dark:bg-white dark:text-black"
          >
            {checkingOut ? "Redirecting to checkout..." : "Checkout"}
          </button>
        </>
      )}
    </div>
  );
}

export default function CartPage() {
  return (
    <RequireAuth>
      <CartContent />
    </RequireAuth>
  );
}
