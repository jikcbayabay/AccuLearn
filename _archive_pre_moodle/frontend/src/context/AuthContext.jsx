import React, { createContext, useContext, useEffect, useState } from 'react';
import * as authService from '../services/authService.js';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  // On mount: if we have a stored token, validate it against the backend.
  // Trusting only localStorage causes a "ghost session" — the UI appears
  // logged in until the first protected call 401s and boots the user.
  useEffect(() => {
    let cancelled = false;
    const token = localStorage.getItem('acculearn_token');

    if (!token) {
      setIsLoading(false);
      return;
    }

    authService.me()
      .then((fresh) => {
        if (cancelled) return;
        setUser(fresh);
        localStorage.setItem('acculearn_user', JSON.stringify(fresh));
      })
      .catch(() => {
        if (cancelled) return;
        localStorage.removeItem('acculearn_token');
        localStorage.removeItem('acculearn_user');
        setUser(null);
      })
      .finally(() => {
        if (!cancelled) setIsLoading(false);
      });

    return () => { cancelled = true; };
  }, []);

  // Listen for 401s from the axios interceptor
  useEffect(() => {
    const handler = () => {
      setUser(null);
    };
    window.addEventListener('acculearn:unauthorized', handler);
    return () => window.removeEventListener('acculearn:unauthorized', handler);
  }, []);

  const login = async (email, password) => {
    const u = await authService.login(email, password);
    setUser(u);
    return u;
  };

  const register = async (payload) => {
    const u = await authService.register(payload);
    setUser(u);
    return u;
  };

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  const value = {
    user,
    isAuthenticated: !!user,
    isLoading,
    login,
    register,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used inside <AuthProvider>');
  return ctx;
}
