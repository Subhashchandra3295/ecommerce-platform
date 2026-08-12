"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { api, ApiError } from "@/lib/api";
import { formatCents } from "@/lib/money";
import type { Category, Product } from "@/lib/types";

export default function HomePage() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [products, setProducts] = useState<Product[] | null>(null);
  const [activeCategory, setActiveCategory] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    api.listCategories().then(setCategories).catch(() => {});
  }, []);

  useEffect(() => {
    setProducts(null);
    api
      .listProducts({ category: activeCategory ?? undefined })
      .then((res) => setProducts(res.data))
      .catch((err) => setError(err instanceof ApiError ? err.message : "Failed to load products"));
  }, [activeCategory]);

  return (
    <div className="flex flex-col gap-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Everyday goods, thoughtfully made.</h1>
        <p className="mt-2 text-black/60 dark:text-white/60">
          A small storefront demonstrating a real Laravel + Stripe checkout flow.
        </p>
      </div>

      <div className="flex flex-wrap gap-2">
        <button
          onClick={() => setActiveCategory(null)}
          className={`rounded-full border px-4 py-1.5 text-sm ${
            activeCategory === null
              ? "border-black bg-black text-white dark:border-white dark:bg-white dark:text-black"
              : "border-black/15 dark:border-white/20"
          }`}
        >
          All
        </button>
        {categories.map((cat) => (
          <button
            key={cat.id}
            onClick={() => setActiveCategory(cat.slug)}
            className={`rounded-full border px-4 py-1.5 text-sm ${
              activeCategory === cat.slug
                ? "border-black bg-black text-white dark:border-white dark:bg-white dark:text-black"
                : "border-black/15 dark:border-white/20"
            }`}
          >
            {cat.name}
          </button>
        ))}
      </div>

      {error && <p className="text-sm text-red-600">{error}</p>}

      {products === null ? (
        <p className="text-sm text-black/60 dark:text-white/60">Loading products...</p>
      ) : (
        <div className="grid grid-cols-2 gap-6 sm:grid-cols-3">
          {products.map((product) => (
            <Link
              key={product.id}
              href={`/products/${product.slug}`}
              className="flex flex-col gap-1 rounded-xl border border-black/10 p-4 hover:border-black/30 dark:border-white/10 dark:hover:border-white/30"
            >
              <p className="text-xs uppercase tracking-wide text-black/50 dark:text-white/50">
                {product.category?.name}
              </p>
              <p className="font-medium">{product.name}</p>
              <p className="text-sm text-black/60 dark:text-white/60">{formatCents(product.price_cents)}</p>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
