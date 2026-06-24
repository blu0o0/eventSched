import { ref, type Ref } from 'vue';
import { useToast } from '@/composables/useToast';

export function useApi<T>() {
  const loading = ref(false) as Ref<boolean>;
  const error = ref<string | null>(null);
  const toast = useToast();

  const execute = async (fn: () => Promise<T>): Promise<T | null> => {
    loading.value = true;
    error.value = null;

    try {
      const result = await fn();
      return result;
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'An error occurred';
      toast.show(error.value, 'danger');
      return null;
    } finally {
      loading.value = false;
    }
  };

  const showSuccess = (message: string) => {
    toast.show(message, 'success');
  };

  return {
    loading,
    execute,
    showSuccess,
    error,
  };
}