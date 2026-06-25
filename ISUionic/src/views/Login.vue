<template>
  <ion-page>
    <ion-content :fullscreen="true" class="login-content">
      <div class="login-container">
        <div class="login-card">
          <div class="login-header">
            <div class="logo-container">
              <div class="logo-circle">
                <img src="https://th.bing.com/th/id/OIP.YFWeW9_VhHAAEQFvvsJxhgAAAA?o=7&rm=3&rs=1&pid=ImgDetMain" alt="ISU Logo" class="logo-image">
              </div>
              <div class="logo-text">
                <h3>Event Scheduling Reservation System - ISU Santiago</h3>
                <p>Event Scheduling & Management</p>
              </div>
            </div>
          </div>

          <div class="login-body">
            <form @submit.prevent="handleLogin">
              <div class="form-group">
                <ion-input
                  v-model="form.email"
                  type="email"
                  placeholder="Email"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.email }"
                  @ion-input="clearError('email')"
                ></ion-input>
                <ion-note v-if="errors.email" color="danger" class="error-note">{{ errors.email }}</ion-note>
              </div>

              <div class="form-group password-group">
                <ion-input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Password"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.password }"
                  @ion-input="clearError('password')"
                ></ion-input>
                <ion-button
                  fill="clear"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                >
                  <ion-icon :icon="showPassword ? eyeOffOutline : eyeOutline"></ion-icon>
                </ion-button>
                <ion-note v-if="errors.password" color="danger" class="error-note">{{ errors.password }}</ion-note>
              </div>

              <input type="hidden" name="recaptcha_token" id="recaptcha_token">

              <ion-button
                expand="block"
                type="submit"
                :disabled="loading || !isFormValid"
                class="btn-login"
              >
                <ion-spinner v-if="loading" name="crescent"></ion-spinner>
                <span v-else>Login</span>
              </ion-button>
            </form>

            <div class="links">
              <ion-button fill="clear" @click="$router.push('/forgot-password')" class="forgot-link">
                Forgot Password?
              </ion-button>
              <ion-button fill="clear" @click="$router.push('/register')" class="register-link">
                Don't have an account? Register
              </ion-button>
            </div>
          </div>

          <div class="login-footer">
            <p class="footer-text">
              Event Scheduling Reservation System - ISU Santiago. Copyright © {{ new Date().getFullYear() }}. Developed by ISU Team.
            </p>
            <div class="security-badge">
              <ion-icon :icon="shieldCheckmarkOutline"></ion-icon>
              <span>VERIFIED & SECURED</span>
            </div>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
// Declare grecaptcha global type
declare global {
  interface Window {
    grecaptcha: {
      ready: (callback: () => void) => void;
      execute: (siteKey: string, options: { action: string }) => Promise<string>;
      reset: () => void;
    };
  }
}

import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
  IonPage,
  IonContent,
  IonInput,
  IonButton,
  IonNote,
  IonSpinner,
  IonIcon,
  toastController,
} from '@ionic/vue';
import { shieldCheckmarkOutline, eyeOutline, eyeOffOutline } from 'ionicons/icons';
import { useAuth } from '../composables/useAuth';
import { validators } from '../utils/validators';
import { RECAPTCHA_SITE_KEY } from '../config/env';

const { login, isLoading } = useAuth();
const loading = isLoading;

const form = ref({
  email: '',
  password: '',
});

const showPassword = ref(false);

const errors = ref<Record<string, string>>({});

const isFormValid = computed(() => {
  return (
    validators.required(form.value.email) &&
    validators.email(form.value.email) &&
    validators.required(form.value.password)
  );
});

function clearError(field: string) {
  if (errors.value[field]) {
    delete errors.value[field];
  }
}

function validateForm(): boolean {
  errors.value = {};

  if (!validators.required(form.value.email)) {
    errors.value.email = 'Email is required';
  } else if (!validators.email(form.value.email)) {
    errors.value.email = 'Please enter a valid email';
  }

  if (!validators.required(form.value.password)) {
    errors.value.password = 'Password is required';
  }

  return Object.keys(errors.value).length === 0;
}

async function handleLogin() {
  if (!validateForm()) {
    return;
  }

  try {
    // Get reCAPTCHA v3 token
    const recaptchaToken = await getRecaptchaToken();
    
    await login(form.value.email, form.value.password, false, recaptchaToken);
    const toast = await toastController.create({
      message: 'Login successful!',
      duration: 2000,
      position: 'bottom',
      color: 'success',
    });
    await toast.present();
  } catch (error: any) {
    const errorMessage =
      error.response?.data?.message || error.message || 'Login failed. Please try again.';
    const toast = await toastController.create({
      message: errorMessage,
      duration: 3000,
      position: 'bottom',
      color: 'danger',
    });
    await toast.present();
  }
}

async function getRecaptchaToken(): Promise<string> {
  return new Promise((resolve, reject) => {
    if (!window.grecaptcha) {
      reject(new Error('reCAPTCHA not loaded'));
      return;
    }

    window.grecaptcha.ready(function() {
      window.grecaptcha.execute(RECAPTCHA_SITE_KEY, {action: 'login'}).then(function(token: string) {
        resolve(token);
      }).catch(reject);
    });
  });
}

// Show reCAPTCHA badge when on login page
onMounted(() => {
  const badge = document.querySelector('.grecaptcha-badge') as HTMLElement | null;
  if (badge) {
    badge.style.visibility = 'visible';
    badge.style.opacity = '1';
    badge.style.pointerEvents = 'auto';
  }
});

onUnmounted(() => {
  const badge = document.querySelector('.grecaptcha-badge') as HTMLElement | null;
  if (badge) {
    badge.style.visibility = '';
    badge.style.opacity = '';
    badge.style.pointerEvents = '';
  }
});
</script>

<style scoped>
.login-content {
  --background: #001829;
  position: relative;
}

.login-content::part(background) {
  background: #001829;
}

.login-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 50%, rgba(45, 134, 89, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(30, 93, 63, 0.05) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

.login-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100%;
  padding: 2rem 1rem;
  position: relative;
  z-index: 1;
}

.login-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 450px;
  width: 100%;
  overflow: hidden;
}

.login-header {
  padding: 30px 30px 20px;
  border-bottom: 1px solid #f0f0f0;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
}

.logo-circle {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 50%;
}

.logo-text {
  flex: 1;
}

.logo-text h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.2;
}

.logo-text p {
  margin: 0;
  font-size: 12px;
  color: #6c757d;
}

.login-body {
  padding: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.password-group {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  --background: transparent;
  --background-hover: transparent;
  --color: #2d8659;
  --color-hover: #1e5d3f;
  font-size: 20px;
  z-index: 10;
  margin: 0;
  padding: 5px;
  min-width: 32px;
  height: 32px;
  --border-color: transparent;
  --box-shadow: none;
}

.password-toggle:hover {
  --color: #1e5d3f;
}

.custom-input {
  --background: #e8f5e9;
  --color: #1e5d3f;
  --placeholder-color: #81c784;
  --padding-start: 18px;
  --padding-end: 18px;
  --padding-top: 14px;
  --padding-bottom: 14px;
  border-radius: 12px;
  font-size: 15px;
  border: 2px solid #c8e6c9;
  transition: all 0.3s ease;
}

.password-group .custom-input {
  --padding-end: 50px;
}

.custom-input.ion-invalid {
  --background: #ffebee;
  --border-color: #dc3545;
}

.custom-input:focus-within {
  border-color: #2d8659;
  box-shadow: 0 0 0 0.2rem rgba(45, 134, 89, 0.15);
}

.error-note {
  margin-top: 5px;
  font-size: 12px;
  padding-left: 18px;
}

.btn-login {
  --background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  --color: white;
  --border-radius: 12px;
  --padding-top: 14px;
  --padding-bottom: 14px;
  margin-top: 10px;
  font-weight: 600;
  font-size: 16px;
  height: 48px;
  box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.btn-login::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-login:hover::before {
  left: 100%;
}

.btn-login:hover {
  --background: linear-gradient(135deg, #1e5d3f 0%, #2d8659 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(45, 134, 89, 0.4);
}

.btn-login:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(45, 134, 89, 0.3);
}

.btn-login:disabled {
  --background: #6c757d;
  opacity: 0.6;
  transform: none;
}

.links {
  margin-top: 1.5rem;
  text-align: center;
}

.forgot-link {
  --color: #2d8659;
  font-size: 13px;
  font-weight: 400;
  margin-bottom: 4px;
}

.register-link {
  --color: #2d8659;
  font-size: 14px;
  font-weight: 500;
}

.login-footer {
  padding: 20px 30px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
  text-align: center;
}

.footer-text {
  font-size: 11px;
  color: #6c757d;
  margin: 0;
  line-height: 1.5;
}

.security-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #1e5d3f 0%, #2d8659 100%);
  color: white;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 600;
  margin-top: 10px;
  box-shadow: 0 2px 8px rgba(45, 134, 89, 0.3);
}

.security-badge ion-icon {
  color: #ffc107;
  font-size: 16px;
}

</style>