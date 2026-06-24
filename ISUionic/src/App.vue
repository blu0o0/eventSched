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
  --background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}
</style>
