"use client";

import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from "react";
import { api } from "@/lib/api";
import type { Cart } from "@/lib/types";
import { useAuth } from "./AuthContext";

interface CartContextValue {
  cart: Cart | null;
  itemCount: number;
  refresh: () => Promise<void>;
  addItem: (productId: number, quantity?: number) => Promise<void>;
  updateItem: (itemId: number, quantity: number) => Promise<void>;
  removeItem: (itemId: number) => Promise<void>;
}

const CartContext = createContext<CartContextValue | null>(null);

export function CartProvider({ children }: { children: ReactNode }) {
  const { user } = useAuth();
  const [cart, setCart] = useState<Cart | null>(null);

  const refresh = useCallback(async () => {
    if (!user) {
      setCart(null);
      return;
    }
    const data = await api.getCart();
    setCart(data);
  }, [user]);

  useEffect(() => {
    refresh().catch(() => setCart(null));
  }, [refresh]);

  const addItem = useCallback(
    async (productId: number, quantity = 1) => {
      const data = await api.addCartItem(productId, quantity);
      setCart(data);
    },
    [],
  );

  const updateItem = useCallback(async (itemId: number, quantity: number) => {
    const data = await api.updateCartItem(itemId, quantity);
    setCart(data);
  }, []);

  const removeItem = useCallback(async (itemId: number) => {
    const data = await api.removeCartItem(itemId);
    setCart(data);
  }, []);

  const itemCount = cart?.items.reduce((sum, item) => sum + item.quantity, 0) ?? 0;

  return (
    <CartContext.Provider value={{ cart, itemCount, refresh, addItem, updateItem, removeItem }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart(): CartContextValue {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error("useCart must be used within CartProvider");
  return ctx;
}
