<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-title>Register</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="register-content">
      <div class="register-container">
        <div class="register-card">
          <div class="register-header">
            <div class="logo-container">
              <div class="logo-circle">
                <img src="https://th.bing.com/th/id/OIP.YFWeW9_VhHAAEQFvvsJxhgAAAA?o=7&rm=3&rs=1&pid=ImgDetMain" alt="ISU Logo" class="logo-image">
              </div>
              <div class="logo-text">
                <h3>ISU Reservation System</h3>
                <p>Create Your Account</p>
              </div>
            </div>
          </div>

          <div class="register-body">

            <form @submit.prevent="handleRegister">
              <div class="form-group">
                <ion-input
                  v-model="form.name"
                  type="text"
                  placeholder="Full Name"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.name }"
                  @ion-input="clearError('name')"
                ></ion-input>
                <ion-note v-if="errors.name" color="danger" class="error-note">{{ errors.name }}</ion-note>
              </div>

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

              <div class="form-group">
                <ion-input
                  v-model="form.password"
                  type="password"
                  placeholder="Password"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.password }"
                  @ion-input="clearError('password')"
                ></ion-input>
                <ion-note v-if="errors.password" color="danger" class="error-note">{{ errors.password }}</ion-note>
                <ion-note color="medium" class="helper-note">Minimum 8 characters</ion-note>
              </div>

              <div class="form-group">
                <ion-input
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="Confirm Password"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.password_confirmation }"
                  @ion-input="clearError('password_confirmation')"
                ></ion-input>
                <ion-note v-if="errors.password_confirmation" color="danger" class="error-note">
                  {{ errors.password_confirmation }}
                </ion-note>
              </div>

              <div class="form-group">
                <ion-select
                  v-model="form.role"
                  placeholder="Select Role"
                  interface="popover"
                  class="custom-select"
                >
                  <ion-select-option value="general_user">General User</ion-select-option>
                  <ion-select-option value="main_proponent">Main Proponent</ion-select-option>
                </ion-select>
              </div>

              <ion-button
                expand="block"
                type="submit"
                :disabled="loading || !isFormValid"
                class="btn-register"
              >
                <ion-spinner v-if="loading" name="crescent"></ion-spinner>
                <span v-else>Register</span>
              </ion-button>
            </form>

            <div class="links">
              <ion-button fill="clear" @click="$router.push('/login')" class="login-link">
                Already have an account? Login
              </ion-button>
            </div>
          </div>

          <div class="register-footer">
            <p class="footer-text">
              ISU Reservation System. Copyright © {{ new Date().getFullYear() }}. Developed by ISU Team.
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
import { ref, computed } from 'vue';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonInput,
  IonButton,
  IonNote,
  IonSpinner,
  IonSelect,
  IonSelectOption,
  IonIcon,
  toastController,
} from '@ionic/vue';
import { shieldCheckmarkOutline } from 'ionicons/icons';
import { useAuth } from '../composables/useAuth';
import { validators } from '../utils/validators';

const { register, isLoading } = useAuth();
const loading = isLoading;

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'general_user' as 'main_proponent' | 'general_user',
});

const errors = ref<Record<string, string>>({});

const isFormValid = computed(() => {
  return (
    validators.required(form.value.name) &&
    validators.required(form.value.email) &&
    validators.email(form.value.email) &&
    validators.required(form.value.password) &&
    validators.password(form.value.password) &&
    validators.required(form.value.password_confirmation) &&
    validators.passwordMatch(form.value.password, form.value.password_confirmation)
  );
});

function clearError(field: string) {
  if (errors.value[field]) {
    delete errors.value[field];
  }
}

function validateForm(): boolean {
  errors.value = {};

  if (!validators.required(form.value.name)) {
    errors.value.name = 'Name is required';
  }

  if (!validators.required(form.value.email)) {
    errors.value.email = 'Email is required';
  } else if (!validators.email(form.value.email)) {
    errors.value.email = 'Please enter a valid email';
  }

  if (!validators.required(form.value.password)) {
    errors.value.password = 'Password is required';
  } else if (!validators.password(form.value.password)) {
    errors.value.password = 'Password must be at least 8 characters';
  }

  if (!validators.required(form.value.password_confirmation)) {
    errors.value.password_confirmation = 'Please confirm your password';
  } else if (
    !validators.passwordMatch(form.value.password, form.value.password_confirmation)
  ) {
    errors.value.password_confirmation = 'Passwords do not match';
  }

  return Object.keys(errors.value).length === 0;
}

async function handleRegister() {
  if (!validateForm()) {
    return;
  }

  try {
    await register({
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      role: form.value.role,
    });
    const toast = await toastController.create({
      message: 'Registration successful!',
      duration: 2000,
      position: 'bottom',
      color: 'success',
    });
    await toast.present();
  } catch (error: any) {
    const errorMessage =
      error.response?.data?.message || error.message || 'Registration failed. Please try again.';
    const toast = await toastController.create({
      message: errorMessage,
      duration: 3000,
      position: 'bottom',
      color: 'danger',
    });
    await toast.present();
  }
}
</script>

<style scoped>
.register-content {
  --background: #001829;
  position: relative;
}

.register-content::part(background) {
  background: #001829;
}

.register-content::before {
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

.register-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100%;
  padding: 2rem 1rem;
}

.register-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 450px;
  width: 100%;
  overflow: hidden;
}

.register-header {
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

.register-body {
  padding: 30px;
}

.form-group {
  margin-bottom: 20px;
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

.custom-input.ion-invalid {
  --background: #ffebee;
  --border-color: #dc3545;
}

.custom-input:focus-within {
  --background: #ffffff;
  border-color: #2d8659;
  box-shadow: 0 0 0 0.2rem rgba(45, 134, 89, 0.15);
}

.custom-select {
  --background: #e8f5e9;
  --color: #1e5d3f;
  border: 2px solid #c8e6c9;
  border-radius: 12px;
  padding: 14px 18px;
}

.error-note {
  margin-top: 5px;
  font-size: 12px;
  padding-left: 18px;
}

.helper-note {
  margin-top: 5px;
  font-size: 11px;
  padding-left: 18px;
  color: #6c757d;
}

.btn-register {
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
}

.btn-register:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(45, 134, 89, 0.4);
}

.btn-register:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(45, 134, 89, 0.3);
}

.btn-register:disabled {
  --background: #6c757d;
  opacity: 0.6;
  transform: none;
}

.links {
  margin-top: 1.5rem;
  text-align: center;
}

.login-link {
  --color: #2d8659;
  font-size: 14px;
  font-weight: 500;
}

.register-footer {
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

