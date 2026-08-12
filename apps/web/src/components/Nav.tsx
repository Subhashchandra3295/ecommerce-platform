"use client";

import Link from "next/link";
import { useAuth } from "@/context/AuthContext";
import { useCart } from "@/context/CartContext";

export function Nav() {
  const { user, logout } = useAuth();
  const { itemCount } = useCart();

  return (
    <header className="border-b border-black/10 dark:border-white/10">
      <div className="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
        <Link href="/" className="font-semibold">
          ShopCraft
        </Link>
        <nav className="flex items-center gap-4 text-sm">
          <Link href="/cart" className="hover:underline">
            Cart{itemCount > 0 ? ` (${itemCount})` : ""}
          </Link>
          {user ? (
            <>
              <Link href="/orders" className="hover:underline">
                Orders
              </Link>
              <span className="text-black/60 dark:text-white/60">{user.name}</span>
              <button
                onClick={logout}
                className="rounded-md border border-black/10 px-3 py-1.5 hover:bg-black/5 dark:border-white/15 dark:hover:bg-white/10"
              >
                Log out
              </button>
            </>
          ) : (
            <>
              <Link href="/login" className="hover:underline">
                Log in
              </Link>
              <Link
                href="/register"
                className="rounded-md bg-black px-3 py-1.5 text-white dark:bg-white dark:text-black"
              >
                Sign up
              </Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}
