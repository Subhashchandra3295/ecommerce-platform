"use client";

import { use, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { api, ApiError } from "@/lib/api";
import { formatCents } from "@/lib/money";
import { useAuth } from "@/context/AuthContext";
import { useCart } from "@/context/CartContext";
import type { Product } from "@/lib/types";

function ProductDetail({ slug }: { slug: string }) {
  const { user } = useAuth();
  const { addItem } = useCart();
  const router = useRouter();
  const [product, setProduct] = useState<Product | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [adding, setAdding] = useState(false);
  const [added, setAdded] = useState(false);

  useEffect(() => {
    api
      .getProduct(slug)
      .then(setProduct)
      .catch((err) => setError(err instanceof ApiError ? err.message : "Product not found"));
  }, [slug]);

  async function handleAddToCart() {
    if (!user) {
      router.push("/login");
      return;
    }
    setAdding(true);
    setError(null);
    try {
      await addItem(product!.id, 1);
      setAdded(true);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Failed to add to cart");
    } finally {
      setAdding(false);
    }
  }

  if (error && !product) {
    return <p className="text-sm text-red-600">{error}</p>;
  }

  if (!product) {
    return <p className="text-sm text-black/60 dark:text-white/60">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      <p className="text-xs uppercase tracking-wide text-black/50 dark:text-white/50">
        {product.category?.name}
      </p>
      <h1 className="text-2xl font-bold">{product.name}</h1>
      <p className="text-xl">{formatCents(product.price_cents)}</p>
      {product.description && (
        <p className="max-w-xl text-black/70 dark:text-white/70">{product.description}</p>
      )}
      <p className="text-sm text-black/50 dark:text-white/50">
        {product.stock > 0 ? `${product.stock} in stock` : "Out of stock"}
      </p>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <button
        onClick={handleAddToCart}
        disabled={adding || product.stock === 0}
        className="mt-2 w-fit rounded-md bg-black px-6 py-3 text-sm font-medium text-white disabled:opacity-50 dark:bg-white dark:text-black"
      >
        {added ? "Added to cart" : adding ? "Adding..." : "Add to cart"}
      </button>
    </div>
  );
}

export default function ProductPage({ params }: PageProps<"/products/[slug]">) {
  const { slug } = use(params);
  return <ProductDetail slug={slug} />;
}
