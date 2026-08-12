export interface Category {
  id: number;
  name: string;
  slug: string;
}

export interface Product {
  id: number;
  category_id: number;
  name: string;
  slug: string;
  description: string | null;
  price_cents: number;
  stock: number;
  image_path: string | null;
  category?: Category;
}

export interface ProductListResponse {
  data: Product[];
  current_page: number;
  last_page: number;
  total: number;
}

export interface CartItem {
  id: number;
  cart_id: number;
  product_id: number;
  quantity: number;
  product: Product;
}

export interface Cart {
  id: number;
  user_id: number;
  items: CartItem[];
}

export type OrderStatus = "pending" | "paid" | "fulfilled" | "cancelled";

export interface OrderItem {
  id: number;
  product_id: number | null;
  product_name: string;
  unit_price_cents: number;
  quantity: number;
}

export interface Order {
  id: number;
  status: OrderStatus;
  total_cents: number;
  created_at: string;
  items: OrderItem[];
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
}

export interface AuthResponse {
  token: string;
  user: AuthUser;
}
