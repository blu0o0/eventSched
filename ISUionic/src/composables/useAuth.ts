import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

export function useAuth() {
  const router = useRouter();
  const authStore = useAuthStore();

  const user = computed(() => authStore.user);
  const isAuthenticated = computed(() => authStore.isAuthenticated);
  const isLoading = computed(() => authStore.isLoading);

  const login = async (email: string, password: string, rememberMe: boolean = false, recaptchaToken?: string) => {
    try {
      await authStore.login(email, password, rememberMe, recaptchaToken);
      router.push('/home');
    } catch (error: any) {
      throw error;
    }
  };

  const register = async (data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role?: 'main_proponent' | 'general_user';
    email_verified: boolean;
  }) => {
    try {
      await authStore.register(data);
      router.push('/home');
    } catch (error: any) {
      throw error;
    }
  };

  const logout = async () => {
    await authStore.logout();
    router.push('/login');
  };

  return {
    user,
    isAuthenticated,
    isLoading,
    login,
    register,
    logout,
  };
}