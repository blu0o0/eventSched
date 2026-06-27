import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { User } from '../types';
import { authApi } from '../api/auth';
import { storage } from '../utils/storage';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(null);
  const isLoading = ref(false);
  const isInitialized = ref(false);

  const isAuthenticated = computed(() => !!token.value && !!user.value);

  async function login(email: string, password: string, rememberMe: boolean = false, recaptchaToken?: string) {
    isLoading.value = true;
    try {
      const response = await authApi.login({ email, password, remember_me: rememberMe, recaptcha_token: recaptchaToken });
      token.value = response.token;
      user.value = response.user;
      await storage.setToken(response.token);
      await storage.setUser(response.user);
      await storage.setRememberMe(rememberMe);
      isInitialized.value = true;
      return response;
    } finally {
      isLoading.value = false;
    }
  }

  async function register(data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role?: 'main_proponent' | 'general_user';
    email_verified: boolean;
  }) {
    isLoading.value = true;
    try {
      const response = await authApi.register(data);
      token.value = response.token;
      user.value = response.user;
      await storage.setToken(response.token);
      await storage.setUser(response.user);
      isInitialized.value = true;
      return response;
    } finally {
      isLoading.value = false;
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await authApi.logout();
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      token.value = null;
      user.value = null;
      isInitialized.value = false;
      await storage.clear();
    }
  }

  async function checkAuth() {
    if (isInitialized.value) {
      return isAuthenticated.value;
    }
    
    isLoading.value = true;
    try {
      const storedToken = await storage.getToken();
      const storedUser = await storage.getUser();

      if (storedToken && storedUser) {
        token.value = storedToken;
        user.value = storedUser;
        isInitialized.value = true;
        
        authApi.getCurrentUser()
          .then((currentUser) => {
            user.value = currentUser;
            storage.setUser(currentUser);
          })
          .catch((error) => {
            console.error('Token validation failed:', error);
          });
        
        return true;
      }
      isInitialized.value = true;
      return false;
    } catch (error) {
      console.error('Auth check error:', error);
      token.value = null;
      user.value = null;
      isInitialized.value = true;
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateUser() {
    try {
      const currentUser = await authApi.getCurrentUser();
      user.value = currentUser;
      await storage.setUser(currentUser);
    } catch (error) {
      console.error('Update user error:', error);
    }
  }

  async function updateName(name: string) {
    try {
      const response = await authApi.updateName(name);
      user.value = response.user;
      await storage.setUser(response.user);
      return response;
    } catch (error) {
      console.error('Update name error:', error);
      throw error;
    }
  }

  function clearAuth() {
    token.value = null;
    user.value = null;
    isInitialized.value = false;
  }

  return {
    user,
    token,
    isLoading,
    isInitialized,
    isAuthenticated,
    login,
    register,
    logout,
    checkAuth,
    updateUser,
    updateName,
    clearAuth,
  };
});