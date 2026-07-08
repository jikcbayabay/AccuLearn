import api from './api.js';

export const login = async (email, password) => {
  const res = await api.post('/auth/login', { email, password });
  localStorage.setItem('acculearn_token', res.data.token);
  localStorage.setItem('acculearn_user', JSON.stringify(res.data.user));
  return res.data.user;
};

export const register = async ({ name, email, password, password_confirmation, section }) => {
  const res = await api.post('/auth/register', {
    name,
    email,
    password,
    password_confirmation,
    section,
  });
  localStorage.setItem('acculearn_token', res.data.token);
  localStorage.setItem('acculearn_user', JSON.stringify(res.data.user));
  return res.data.user;
};

export const me = async () => {
  const res = await api.get('/auth/me');
  return res.data;
};

export const logout = async () => {
  try {
    await api.post('/auth/logout');
  } finally {
    localStorage.removeItem('acculearn_token');
    localStorage.removeItem('acculearn_user');
  }
};
