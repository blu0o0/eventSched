import { ref, onUnmounted, type Ref } from 'vue';
import apiClient from '../api/client';
import { useAuthStore } from '../stores/auth';
import type { ChangeCheckResult } from '../types';

type ChangeCallback = () => void;

/**
 * A composable that watches for changes on the server and triggers callbacks
 * Uses polling to detect changes (lightweight alternative to WebSockets/Pusher)
 * When external broadcast infrastructure is available, switch to Laravel Echo
 */
export function useRealtime() {
  const isPolling = ref(false);
  const lastCheckTime = ref<string | null>(null);
  const pollingInterval = ref(30000); // 30 seconds default
  let intervalId: ReturnType<typeof setInterval> | null = null;
  const callbacks = new Map<string, ChangeCallback>();
  const authStore = useAuthStore();

  function startPolling(type: string = 'all', callback?: ChangeCallback) {
    if (callback) {
      callbacks.set(type, callback);
    }

    if (isPolling.value) return;

    isPolling.value = true;

    // Initial timestamp
    lastCheckTime.value = new Date().toISOString();

    intervalId = setInterval(async () => {
      await checkForChanges(type);
    }, pollingInterval.value);
  }

  async function checkForChanges(type: string = 'all'): Promise<boolean> {
    if (!lastCheckTime.value) return false;

    try {
      const response = await apiClient.get<ChangeCheckResult>('/changes/check', {
        params: {
          since: lastCheckTime.value,
          type,
        },
      });

      if (response.data.has_changes) {
        lastCheckTime.value = response.data.server_time;
        
        // Trigger all registered callbacks
        callbacks.forEach((cb) => cb());
        return true;
      }

      // Still update server time to keep clocks in sync
      if (response.data.server_time) {
        lastCheckTime.value = response.data.server_time;
      }
    } catch (error) {
      // Silently fail - polling will retry on next interval
      console.debug('Polling check failed:', error);
    }

    return false;
  }

  function setPollingInterval(ms: number) {
    pollingInterval.value = ms;
    if (intervalId) {
      clearInterval(intervalId);
      intervalId = setInterval(async () => {
        await checkForChanges();
      }, pollingInterval.value);
    }
  }

  function stopPolling() {
    if (intervalId) {
      clearInterval(intervalId);
      intervalId = null;
    }
    isPolling.value = false;
  }

  function registerCallback(type: string, callback: ChangeCallback) {
    callbacks.set(type, callback);
  }

  function removeCallback(type: string) {
    callbacks.delete(type);
  }

  // Clean up on component unmount
  onUnmounted(() => {
    stopPolling();
    callbacks.clear();
  });

  return {
    isPolling,
    startPolling,
    stopPolling,
    checkForChanges,
    setPollingInterval,
    registerCallback,
    removeCallback,
    lastCheckTime,
  };
}

/**
 * Auto-refresh composable specifically for list views
 * Provides data fetching and auto-refresh capabilities
 */
export function useAutoRefresh<T>(fetchFn: () => Promise<T>) {
  const data = ref<T | null>(null) as Ref<T | null>;
  const loading = ref(false);
  const error = ref<string | null>(null);
  const realtime = useRealtime();
  const pollingRef = ref<ReturnType<typeof setInterval> | null>(null);

  async function executeFetch() {
    loading.value = true;
    error.value = null;
    try {
      const result = await fetchFn();
      data.value = result;
      return result;
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to fetch data';
      return null;
    } finally {
      loading.value = false;
    }
  }

  function enableAutoRefresh(intervalMs: number = 30000, type: string = 'all') {
    // Use realtime polling for change detection
    realtime.registerCallback(type, async () => {
      await executeFetch();
    });
    realtime.startPolling(type);
    
    // Fallback: periodic full refresh
    pollingRef.value = setInterval(async () => {
      await executeFetch();
    }, intervalMs);
  }

  function disableAutoRefresh() {
    realtime.stopPolling();
    realtime.removeCallback('all');
    if (pollingRef.value) {
      clearInterval(pollingRef.value);
      pollingRef.value = null;
    }
  }

  onUnmounted(() => {
    disableAutoRefresh();
  });

  return {
    data,
    loading,
    error,
    executeFetch,
    enableAutoRefresh,
    disableAutoRefresh,
    realtime,
  };
}