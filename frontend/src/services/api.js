// MOCK: Replace with real axios instance when backend is running.
// When wired up, this will export a configured axios client:
//   import axios from 'axios';
//   const api = axios.create({ baseURL: import.meta.env.VITE_API_URL });
//   api.interceptors.request.use(...) // attach Sanctum token
//   export default api;

const api = {
  baseURL: import.meta.env.VITE_API_URL || '',
  get: (url) => Promise.resolve({ data: null, url, method: 'GET' }),
  post: (url, body) => Promise.resolve({ data: null, url, body, method: 'POST' }),
  put: (url, body) => Promise.resolve({ data: null, url, body, method: 'PUT' }),
  delete: (url) => Promise.resolve({ data: null, url, method: 'DELETE' }),
};

export default api;
