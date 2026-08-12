import type {
  AuthResponse,
  AuthUser,
  Cart,
  Category,
  Order,
  Product,
  ProductListResponse,
} from "./types";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000";
const TOKEN_KEY = "shopcraft_token";
const USER_KEY = "shopcraft_user";

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
  ) {
    super(message);
    this.name = "ApiError";
  }
}

export function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return window.localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
  window.localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken(): void {
  window.localStorage.removeItem(TOKEN_KEY);
  window.localStorage.removeItem(USER_KEY);
}

export function getStoredUser(): AuthUser | null {
  if (typeof window === "undefined") return null;
  const raw = window.localStorage.getItem(USER_KEY);
  return raw ? (JSON.parse(raw) as AuthUser) : null;
}

export function setStoredUser(user: AuthUser): void {
  window.localStorage.setItem(USER_KEY, JSON.stringify(user));
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const token = getToken();
  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    Accept: "application/json",
    ...(options.headers as Record<string, string> | undefined),
  };
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const res = await fetch(`${API_URL}${path}`, { ...options, headers });

  if (res.status === 204) {
    return undefined as T;
  }

  const body = await res.json().catch(() => null);

  if (!res.ok) {
    const message =
      (body && typeof body.message === "string" && body.message) || res.statusText;
    throw new ApiError(message, res.status);
  }

  return body as T;
}

export const api = {
  register: (data: { name: string; email: string; password: string }) =>
    request<AuthResponse>("/api/register", { method: "POST", body: JSON.stringify(data) }),

  login: (data: { email: string; password: string }) =>
    request<AuthResponse>("/api/login", { method: "POST", body: JSON.stringify(data) }),

  logout: () => request<void>("/api/logout", { method: "POST" }),

  me: () => request<AuthUser>("/api/me"),

  listCategories: () => request<Category[]>("/api/categories"),

  listProducts: (params: { category?: string; search?: string; page?: number } = {}) => {
    const qs = new URLSearchParams();
    if (params.category) qs.set("category", params.category);
    if (params.search) qs.set("search", params.search);
    if (params.page) qs.set("page", String(params.page));
    const suffix = qs.toString() ? `?${qs.toString()}` : "";
    return request<ProductListResponse>(`/api/products${suffix}`);
  },

  getProduct: (slug: string) => request<Product>(`/api/products/${slug}`),

  getCart: () => request<Cart>("/api/cart"),

  addCartItem: (productId: number, quantity: number) =>
    request<Cart>("/api/cart/items", {
      method: "POST",
      body: JSON.stringify({ product_id: productId, quantity }),
    }),

  updateCartItem: (itemId: number, quantity: number) =>
    request<Cart>(`/api/cart/items/${itemId}`, {
      method: "PATCH",
      body: JSON.stringify({ quantity }),
    }),

  removeCartItem: (itemId: number) =>
    request<Cart>(`/api/cart/items/${itemId}`, { method: "DELETE" }),

  checkout: () => request<{ checkout_url: string }>("/api/checkout", { method: "POST" }),

  listOrders: () => request<Order[]>("/api/orders"),

  getOrder: (id: number | string) => request<Order>(`/api/orders/${id}`),
};
