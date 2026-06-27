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

              <div class="form-group email-group">
                <div class="email-row">
                  <ion-input
                    v-model="form.email"
                    type="email"
                    placeholder="Email"
                    class="custom-input email-input"
                    :class="{ 'ion-invalid': errors.email }"
                    @ion-input="clearError('email')"
                  ></ion-input>
                  <ion-button
                    class="btn-send-code"
                    :disabled="otpSending || !form.email || cooldown > 0"
                    @click="handleSendOtp"
                  >
                    <ion-spinner v-if="otpSending" name="crescent"></ion-spinner>
                    <span v-else>{{ cooldown > 0 ? `${cooldown}s` : (otpSent ? 'Resend' : 'Send Code') }}</span>
                  </ion-button>
                </div>
                <ion-note v-if="errors.email" color="danger" class="error-note">{{ errors.email }}</ion-note>
                <ion-note v-if="otpSent && !emailVerified" color="success" class="helper-note">
                  Verification code sent! Please check your email.
                </ion-note>
              </div>

              <div v-if="otpSent" class="form-group otp-group">
                <div class="otp-row">
                  <ion-input
                    v-model="form.otp"
                    type="text"
                    inputmode="numeric"
                    :maxlength="6"
                    placeholder="Enter 6-digit code"
                    class="custom-input otp-input"
                    :class="{ 'ion-invalid': errors.otp }"
                    @ion-input="clearError('otp')"
                  ></ion-input>
                  <ion-button
                    class="btn-verify-code"
                    :disabled="otpVerifying || form.otp.length !== 6 || emailVerified"
                    @click="handleVerifyOtp"
                  >
                    <ion-spinner v-if="otpVerifying" name="crescent"></ion-spinner>
                    <span v-else>{{ emailVerified ? 'Verified' : 'Verify' }}</span>
                  </ion-button>
                </div>
                <ion-note v-if="errors.otp" color="danger" class="error-note">{{ errors.otp }}</ion-note>
                <ion-note v-if="emailVerified" color="success" class="helper-note">
                  <ion-icon :icon="checkmarkCircle"></ion-icon> Email verified successfully!
                </ion-note>
              </div>

              <div class="form-group password-group">
                <ion-input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Password"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.password || (passwordTouched && !validators.passwordStrong(form.password)) }"
                  @ion-input="clearError('password'); passwordTouched = true; validatePassword();"
                ></ion-input>
                <ion-button
                  fill="clear"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                >
                  <ion-icon :icon="showPassword ? eyeOff : eye"></ion-icon>
                </ion-button>
                <ion-note v-if="passwordError" color="danger" class="error-note">{{ passwordError }}</ion-note>
                <ion-note v-else-if="errors.password" color="danger" class="error-note">{{ errors.password }}</ion-note>
              </div>

              <div class="form-group password-group">
                <ion-input
                  v-model="form.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="Confirm Password"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.password_confirmation }"
                  @ion-input="clearError('password_confirmation')"
                ></ion-input>
                <ion-button
                  fill="clear"
                  class="password-toggle"
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <ion-icon :icon="showConfirmPassword ? eyeOff : eye"></ion-icon>
                </ion-button>
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
                <ion-select-option value="ADMINISTRATOR">Administrator</ion-select-option>
                  <ion-select-option value="SBO BSIT WMAD">SBO BSIT WMAD</ion-select-option>
                  <ion-select-option value="SBO BSIT NETSEC">SBO BSIT NETSEC</ion-select-option>
                  <ion-select-option value="SBO BSA">SBO BSA</ion-select-option>
                  <ion-select-option value="SBL BSLEA">SBL BSLEA</ion-select-option>
                  <ion-select-option value="SSC OFFICER">SSC Officer</ion-select-option>
                  <ion-select-option value="FACULTY/STAFF">Faculty/Staff</ion-select-option>
                  <ion-select-option value="STUDENT">Student</ion-select-option>
                </ion-select>
              </div>

              <ion-button
                expand="block"
                type="submit"
                :disabled="loading || !isFormValid || !emailVerified"
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
import { ref, computed, onUnmounted } from 'vue';
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
import { shieldCheckmarkOutline, eye, eyeOff, checkmarkCircle } from 'ionicons/icons';
import { useAuth } from '../composables/useAuth';
import { validators } from '../utils/validators';
import { authApi } from '../api/auth';

const { register, isLoading } = useAuth();
const loading = isLoading;

const form = ref({
  name: '',
  email: '',
  otp: '',
  password: '',
  password_confirmation: '',
  role: 'STUDENT' as 'ADMINISTRATOR' | 'SBO BSIT WMAD' | 'SBO BSIT NETSEC' | 'SBO BSA' | 'SBL BSLEA' | 'SSC OFFICER' | 'FACULTY/STAFF' | 'STUDENT',
});

const errors = ref<Record<string, string>>({});
const passwordTouched = ref(false);
const passwordError = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const otpSending = ref(false);
const otpSent = ref(false);
const emailVerified = ref(false);
const otpVerifying = ref(false);
const cooldown = ref(0);
let cooldownTimer: number | null = null;

const isFormValid = computed(() => {
  return (
    validators.required(form.value.name) &&
    validators.required(form.value.email) &&
    validators.email(form.value.email) &&
    validators.required(form.value.password) &&
    validators.passwordStrong(form.value.password) &&
    validators.required(form.value.password_confirmation) &&
    validators.passwordMatch(form.value.password, form.value.password_confirmation) &&
    emailVerified.value
  );
});

function clearError(field: string) {
  if (errors.value[field]) {
    delete errors.value[field];
  }
  if (field === 'password') {
    passwordError.value = '';
  }
}

function validatePassword(): void {
  if (!passwordTouched.value || !form.value.password) {
    passwordError.value = '';
    return;
  }

  if (!validators.password(form.value.password)) {
    passwordError.value = 'Password must be at least 8 characters';
  } else if (!validators.hasLetter(form.value.password)) {
    passwordError.value = 'Password must contain at least one letter (a-z, A-Z)';
  } else if (!validators.hasNumber(form.value.password)) {
    passwordError.value = 'Password must contain at least one number (0-9)';
  } else if (!validators.hasSymbol(form.value.password)) {
    passwordError.value = 'Password must contain at least one symbol (@$!%*?&.,)';
  } else {
    passwordError.value = '';
  }
}

function startCooldown() {
  cooldown.value = 60;
  cooldownTimer = window.setInterval(() => {
    cooldown.value--;
    if (cooldown.value <= 0 && cooldownTimer) {
      clearInterval(cooldownTimer);
      cooldownTimer = null;
    }
  }, 1000);
}

async function handleVerifyOtp() {
  errors.value = {};
  
  if (!form.value.otp || form.value.otp.length !== 6) {
    errors.value.otp = 'Please enter the 6-digit code';
    return;
  }

  otpVerifying.value = true;
  try {
    const response = await authApi.verifyEmailOtp(form.value.email, form.value.otp);
    if (response.email_verified) {
      emailVerified.value = true;
      errors.value.otp = '';
      const toast = await toastController.create({
        message: 'Email verified successfully!',
        duration: 2000,
        color: 'success',
      });
      await toast.present();
    } else {
      errors.value.otp = 'Verification failed. Please try again.';
    }
  } catch (err: any) {
    const errorMessage = err.response?.data?.message || 'Invalid verification code.';
    errors.value.otp = errorMessage;
    const toast = await toastController.create({
      message: errorMessage,
      duration: 3000,
      color: 'danger',
    });
    await toast.present();
  } finally {
    otpVerifying.value = false;
  }
}

async function handleSendOtp() {
  errors.value = {};

  if (!form.value.email) {
    errors.value.email = 'Please enter your email first';
    return;
  }

  otpSending.value = true;
  try {
    await authApi.sendVerificationOtp(form.value.email);
    otpSent.value = true;
    emailVerified.value = false;
    form.value.otp = '';
    startCooldown();
    const toast = await toastController.create({
      message: 'Verification code sent to your email!',
      duration: 3000,
      color: 'success',
    });
    await toast.present();
  } catch (err: any) {
    const message = err.response?.data?.message || 'Failed to send verification code.';
    if (err.response?.data?.errors?.email) {
      errors.value.email = err.response.data.errors.email[0];
    } else {
      const toast = await toastController.create({
        message,
        duration: 3000,
        color: 'danger',
      });
      await toast.present();
    }
  } finally {
    otpSending.value = false;
  }
}

async function handleRegister() {
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
  }

  // OTP verification is REQUIRED - check this first before anything else
  if (!emailVerified.value) {
    errors.value.email = 'Please verify your email first by entering the OTP code.';
    return;
  }

  if (!validators.password(form.value.password)) {
    errors.value.password = 'Password must be at least 8 characters';
  } else if (!validators.hasLetter(form.value.password)) {
    errors.value.password = 'Password must contain at least one letter';
  } else if (!validators.hasNumber(form.value.password)) {
    errors.value.password = 'Password must contain at least one number';
  } else if (!validators.hasSymbol(form.value.password)) {
    errors.value.password = 'Password must contain at least one symbol';
  }

  if (!validators.required(form.value.password_confirmation)) {
    errors.value.password_confirmation = 'Please confirm your password';
  } else if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match';
  }

  if (Object.keys(errors.value).length > 0) return;

  try {
    await register({
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      role: form.value.role,
      email_verified: true,
    });
    const toast = await toastController.create({
      message: 'Registration successful!',
      duration: 2000,
      position: 'bottom',
      color: 'success',
    });
    await toast.present();
  } catch (error: any) {
    const errorMessage = error.response?.data?.message || error.message || 'Registration failed. Please try again.';
    const toast = await toastController.create({
      message: errorMessage,
      duration: 3000,
      position: 'bottom',
      color: 'danger',
    });
    await toast.present();
  }
}

onUnmounted(() => {
  if (cooldownTimer) {
    clearInterval(cooldownTimer);
  }
});
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

.email-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.email-input {
  flex: 1;
}

.btn-send-code {
  --background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  --color: white;
  --border-radius: 10px;
  font-size: 12px;
  font-weight: 600;
  height: 44px;
  min-width: 100px;
  --padding-start: 12px;
  --padding-end: 12px;
  margin: 0;
  white-space: nowrap;
}

.btn-send-code:disabled {
  --background: #6c757d;
  opacity: 0.6;
}

.otp-group {
  margin-bottom: 20px;
}

.otp-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.otp-input {
  flex: 1;
}

.btn-verify-code {
  --background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  --color: white;
  --border-radius: 10px;
  font-size: 12px;
  font-weight: 600;
  height: 44px;
  min-width: 100px;
  --padding-start: 12px;
  --padding-end: 12px;
  margin: 0;
  white-space: nowrap;
}

.btn-verify-code:disabled {
  --background: #6c757d;
  opacity: 0.6;
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

.custom-input.ion-invalid {
  --background: #ffebee;
  --border-color: #dc3545;
}

.custom-input:focus-within {
  --background: #ffffff;
  border-color: #2d8659;
  box-shadow: 0 0 0 0.2rem rgba(45, 134, 89, 0.15);
}

.otp-input {
  text-align: center;
  font-size: 24px;
  letter-spacing: 6px;
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