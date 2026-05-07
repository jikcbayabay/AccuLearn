import React, { createContext, useContext, useEffect, useState } from 'react';
import * as authService from '../services/authService.js';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  // Restore session from localStorage on mount
  useEffect(() => {
    try {
      const stored = localStorage.getItem('acculearn_user');
      if (stored) setUser(JSON.parse(stored));
    } catch {
      // ignore malformed JSON
    } finally {
      setIsLoading(false);
    }
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

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  const value = {
    user,
    isAuthenticated: !!user,
    isLoading,
    login,
    logout,
    setUser, // exposed so role-switcher demo can swap users without going through API
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used inside <AuthProvider>');
  return ctx;
}
