import axios, { AxiosInstance, AxiosError, InternalAxiosRequestConfig } from 'axios';
import { API_BASE_URL } from '../config/env';
import { storage } from '../utils/storage';
import router from '../router';

// Create axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor: Add Authorization header with token
apiClient.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    const token = await storage.getToken();
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error: AxiosError) => {
    return Promise.reject(error);
  }
);

// Response interceptor: Handle 401 errors (logout and redirect to login)
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      // Skip if the request was to logout endpoint to avoid recursion
      if (error.config?.url?.includes('/logout')) {
        return Promise.reject(error);
      }
      
      // Clear stored auth data
      await storage.clear();
      
      // Clear auth store state directly (avoid calling logout to prevent recursion)
      const { useAuthStore } = await import('../stores/auth');
      const authStore = useAuthStore();
      authStore.clearAuth();
      
      // Redirect to login only if not already on login page
      if (router.currentRoute.value.path !== '/login') {
        router.push('/login');
      }
    }
    return Promise.reject(error);
  }
);

export default apiClient;

