"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import { useRouter } from "next/navigation";
import { api, clearToken, getStoredUser, getToken, setStoredUser, setToken } from "@/lib/api";
import type { AuthUser } from "@/lib/types";

interface AuthContextValue {
  user: AuthUser | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (data: { name: string; email: string; password: string }) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    const token = getToken();
    const cached = getStoredUser();
    if (!token || !cached) {
      setLoading(false);
      return;
    }
    setUser(cached);
    setLoading(false);

    api.me().catch(() => {
      clearToken();
      setUser(null);
    });
  }, []);

  const login = useCallback(
    async (email: string, password: string) => {
      const res = await api.login({ email, password });
      setToken(res.token);
      setStoredUser(res.user);
      setUser(res.user);
      router.push("/");
    },
    [router],
  );

  const register = useCallback(
    async (data: { name: string; email: string; password: string }) => {
      const res = await api.register(data);
      setToken(res.token);
      setStoredUser(res.user);
      setUser(res.user);
      router.push("/");
    },
    [router],
  );

  const logout = useCallback(() => {
    api.logout().catch(() => {});
    clearToken();
    setUser(null);
    router.push("/login");
  }, [router]);

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
