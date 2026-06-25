import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { toastController, alertController } from '@ionic/vue';

export function useRequireAuth() {
  const router = useRouter();
  const authStore = useAuthStore();
  const isShowingPrompt = ref(false);

  /**
   * Automatically redirects to login page if not authenticated
   * Returns true if user is authenticated, false otherwise
   */
  async function requireAuth(message: string = 'You need to be logged in to perform this action.'): Promise<boolean> {
    // If already authenticated, allow the action
    if (authStore.isAuthenticated) {
      return true;
    }

    // Redirect to login page with redirect parameter
    router.push({
      path: '/login',
      query: { redirect: router.currentRoute.value.fullPath }
    });
    
    return false;
  }

  /**
   * Quick check - returns true if authenticated, shows toast and redirects if not
   */
  async function requireAuthOrToast(message: string = 'Please login to continue'): Promise<boolean> {
    if (authStore.isAuthenticated) {
      return true;
    }

    const toast = await toastController.create({
      message,
      duration: 3000,
      position: 'top',
      color: 'warning',
      buttons: [
        {
          text: 'Login',
          handler: () => {
            router.push({
              path: '/login',
              query: { redirect: router.currentRoute.value.fullPath }
            });
          }
        },
        {
          text: 'Sign Up',
          handler: () => {
            router.push({
              path: '/register',
              query: { redirect: router.currentRoute.value.fullPath }
            });
          }
        }
      ]
    });

    await toast.present();
    return false;
  }

  return {
    requireAuth,
    requireAuthOrToast,
    isAuthenticated: computed(() => authStore.isAuthenticated),
  };
}
