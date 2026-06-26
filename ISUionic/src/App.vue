<template>
  <ion-app>
    <AppMenu />
    <ion-router-outlet id="main-content" />
  </ion-app>
</template>

<script setup lang="ts">
import { IonApp, IonRouterOutlet } from '@ionic/vue';
import AppMenu from './components/AppMenu.vue';
import { useAuthStore } from './stores/auth';
import { storeToRefs } from 'pinia';
import { watch, onMounted } from 'vue';

const authStore = useAuthStore();
const { isAuthenticated } = storeToRefs(authStore);

// Create and manage a global style element to control reCAPTCHA visibility
let recaptchaStyleElement: HTMLStyleElement | null = null;

const updateRecaptchaVisibility = () => {
  if (!recaptchaStyleElement) {
    recaptchaStyleElement = document.createElement('style');
    recaptchaStyleElement.id = 'recaptcha-visibility-control';
    document.head.appendChild(recaptchaStyleElement);
  }

  if (isAuthenticated.value) {
    // Hide reCAPTCHA when authenticated
    recaptchaStyleElement.textContent = `
      .grecaptcha-badge {
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
      }
    `;
  } else {
    // Show reCAPTCHA when not authenticated (remove the hiding styles)
    recaptchaStyleElement.textContent = '';
  }
};

// Watch for changes in authentication state
watch(isAuthenticated, () => {
  updateRecaptchaVisibility();
});

// Set initial state and observe DOM for reCAPTCHA badge
onMounted(() => {
  updateRecaptchaVisibility();
  
  // Observe DOM changes to catch when reCAPTCHA badge is added or modified
  const observer = new MutationObserver(() => {
    updateRecaptchaVisibility();
  });
  
  observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['style', 'class']
  });
});
</script>

<style>
/* Global green gradient toolbar matching Laravel */
ion-toolbar {
  --background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  --color: #ffffff;
}

/* Light gradient page background matching Laravel dashboard */
ion-content {
  --background: linear-gradient(135deg, #f9fbf9 0%, #f3f4f6 100%);
}

/* Custom Alert Dialog Styling for Login Required */
:deep(.auth-required-alert) {
  --background: white;
  --border-radius: 16px;
  --box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  margin: 0 20px;
}

:deep(.auth-required-alert .alert-header) {
  background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  color: white;
  padding: 20px;
  border-radius: 16px 16px 0 0;
}

:deep(.auth-required-alert .alert-title) {
  color: white;
  font-size: 18px;
  font-weight: 600;
  text-align: center;
}

:deep(.auth-required-alert .alert-message) {
  color: #374151;
  font-size: 15px;
  line-height: 1.5;
  padding: 20px;
  text-align: center;
}

:deep(.auth-required-alert .alert-button-group) {
  padding: 0 16px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

:deep(.auth-required-alert .alert-button) {
  border-radius: 10px;
  font-weight: 600;
  font-size: 15px;
  min-height: 44px;
  margin: 0;
  text-transform: none;
}

:deep(.alert-cancel-btn) {
  --color: #6b7280;
  --background: #f3f4f6;
}

:deep(.alert-cancel-btn:hover) {
  --background: #e5e7eb;
}

:deep(.alert-login-btn) {
  --background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  --color: white;
  box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
}

:deep(.alert-login-btn:hover) {
  --background: linear-gradient(135deg, #1e5d3f 0%, #2d8659 100%);
  box-shadow: 0 6px 20px rgba(45, 134, 89, 0.4);
}

:deep(.alert-signup-btn) {
  --background: linear-gradient(135deg, #42a5f5 0%, #1565c0 100%);
  --color: white;
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
}

:deep(.alert-signup-btn:hover) {
  --background: linear-gradient(135deg, #1565c0 0%, #42a5f5 100%);
  box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
}
</style>
