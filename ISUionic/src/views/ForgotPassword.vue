<template>
  <ion-page>
    <ion-content :fullscreen="true" class="forgot-content">
      <div class="forgot-container">
        <div class="forgot-card">
          <div class="forgot-header">
            <div class="icon-circle">
              <ion-icon :icon="lockOpenOutline" class="lock-icon"></ion-icon>
            </div>
            <h2>Forgot Password</h2>
            <p v-if="step === 'email'">Enter your email to receive a reset code.</p>
            <p v-else-if="step === 'otp'">Enter the 6-digit code sent to your email.</p>
            <p v-else>Enter your new password.</p>
          </div>

          <div class="forgot-body">
            <!-- Step 1: Email -->
            <div v-if="step === 'email'">
              <div class="form-group email-group">
                <div class="email-row">
                  <ion-input
                    v-model="email"
                    type="email"
                    placeholder="Email"
                    class="custom-input email-input"
                    :class="{ 'ion-invalid': errors.email }"
                    @ion-input="clearError('email')"
                  ></ion-input>
                  <ion-button
                    class="btn-send-code"
                    :disabled="loading || !email || cooldown > 0"
                    @click="handleSendOtp"
                  >
                    <ion-spinner v-if="loading" name="crescent"></ion-spinner>
                    <span v-else>{{ cooldown > 0 ? `${cooldown}s` : (otpSent ? 'Resend' : 'Send Code') }}</span>
                  </ion-button>
                </div>
                <ion-note v-if="errors.email" color="danger" class="error-note">{{ errors.email }}</ion-note>
              </div>
            </div>

            <!-- Step 2: OTP -->
            <div v-else-if="step === 'otp'">
              <div class="form-group otp-group">
                <div class="otp-row">
                  <ion-input
                    v-model="otp"
                    type="text"
                    inputmode="numeric"
                    :maxlength="6"
                    placeholder="000000"
                    class="custom-input otp-input"
                    :class="{ 'ion-invalid': errors.otp }"
                    @ion-input="clearError('otp')"
                    autofocus
                  ></ion-input>
                  <ion-button
                    class="btn-verify-code"
                    :disabled="otpVerifying || otp.length !== 6"
                    @click="handleVerifyOtp"
                  >
                    <ion-spinner v-if="otpVerifying" name="crescent"></ion-spinner>
                    <span v-else>Verify</span>
                  </ion-button>
                </div>
                <ion-note v-if="errors.otp" color="danger" class="error-note">{{ errors.otp }}</ion-note>
                <ion-note color="medium" class="helper-note">Code sent to {{ email }}</ion-note>
              </div>

              <div class="resend-link">
                <ion-button fill="clear" size="small" :disabled="cooldown > 0" @click="handleResendOtp">
                  {{ cooldown > 0 ? `Resend in ${cooldown}s` : 'Resend Code' }}
                </ion-button>
              </div>
            </div>

            <!-- Step 3: New Password -->
            <div v-else-if="step === 'password'">
              <div class="form-group password-group">
                <ion-input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="New Password"
                  class="custom-input"
                  :class="{ 'ion-invalid': errors.password }"
                  @ion-input="clearError('password')"
                ></ion-input>
                <ion-button
                  fill="clear"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                >
                  <ion-icon :icon="showPassword ? eyeOff : eye"></ion-icon>
                </ion-button>
                <ion-note v-if="errors.password" color="danger" class="error-note">{{ errors.password }}</ion-note>
              </div>

              <div class="form-group password-group">
                <ion-input
                  v-model="form.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="Confirm New Password"
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
                <ion-note v-if="errors.password_confirmation" color="danger" class="error-note">{{ errors.password_confirmation }}</ion-note>
              </div>

              <ion-button
                expand="block"
                class="btn-action"
                :disabled="loading || !form.password || !form.password_confirmation"
                @click="handleResetPassword"
              >
                <ion-spinner v-if="loading" name="crescent"></ion-spinner>
                <span v-else>Reset Password</span>
              </ion-button>
            </div>

            <div class="links">
              <ion-button fill="clear" @click="$router.push('/login')" class="back-link">
                <ion-icon :icon="arrowBackOutline" slot="start"></ion-icon>
                Back to Login
              </ion-button>
            </div>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
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
import { lockOpenOutline, arrowBackOutline, eye, eyeOff } from 'ionicons/icons';
import { authApi } from '../api/auth';
import { validators } from '../utils/validators';

const router = useRouter();

const step = ref<'email' | 'otp' | 'password'>('email');
const email = ref('');
const otp = ref('');
const form = ref({
  password: '',
  password_confirmation: '',
});
const errors = ref<Record<string, string>>({});
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const otpSent = ref(false);
const otpVerifying = ref(false);
const cooldown = ref(0);
let cooldownTimer: number | null = null;

function clearError(field: string) {
  if (errors.value[field]) {
    delete errors.value[field];
  }
}

function startCooldown() {
  cooldown.value = 60;
  if (cooldownTimer) clearInterval(cooldownTimer);
  cooldownTimer = window.setInterval(() => {
    cooldown.value--;
    if (cooldown.value <= 0 && cooldownTimer) {
      clearInterval(cooldownTimer);
      cooldownTimer = null;
    }
  }, 1000);
}

async function handleSendOtp() {
  errors.value = {};

  if (!email.value) {
    errors.value.email = 'Email is required';
    return;
  }

  loading.value = true;
  try {
    await authApi.sendResetOtp(email.value);
    otpSent.value = true;
    step.value = 'otp';
    startCooldown();
    const toast = await toastController.create({
      message: 'Reset code sent to your email!',
      duration: 3000,
      color: 'success',
    });
    await toast.present();
  } catch (err: any) {
    const message = err.response?.data?.message || 'Failed to send reset code.';
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
    loading.value = false;
  }
}

async function handleResendOtp() {
  if (cooldown.value > 0) return;
  await handleSendOtp();
}

  async function handleVerifyOtp() {
  errors.value = {};

  if (otp.value.length !== 6) {
    errors.value.otp = 'Please enter the 6-digit code';
    return;
  }

  otpVerifying.value = true;
  try {
    // Verify OTP only (no password reset yet)
    const response = await authApi.verifyResetOtpOnly({
      email: email.value,
      otp: otp.value,
    });
    
    if (response.otp_valid) {
      step.value = 'password';
      const toast = await toastController.create({
        message: 'Code verified! Please enter your new password.',
        duration: 2000,
        color: 'success',
      });
      await toast.present();
    } else {
      errors.value.otp = 'Verification failed. Please try again.';
    }
  } catch (err: any) {
    const errorMessage = err.response?.data?.message || 'Invalid code.';
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

async function handleResetPassword() {
  errors.value = {};

  if (!validators.required(form.value.password)) {
    errors.value.password = 'Password is required';
    return;
  }
  if (!validators.passwordStrong(form.value.password)) {
    errors.value.password = 'Password must contain: 8+ characters, a letter, a number, and a symbol';
    return;
  }
  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match';
    return;
  }

  loading.value = true;
  try {
    await authApi.resetPassword({
      email: email.value,
      otp: otp.value,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    });

    const toast = await toastController.create({
      message: 'Password has been reset successfully! Please login.',
      duration: 3000,
      color: 'success',
    });
    await toast.present();
    router.push('/login');
  } catch (err: any) {
    const message = err.response?.data?.message || 'Failed to reset password.';
    const toast = await toastController.create({
      message,
      duration: 3000,
      color: 'danger',
    });
    await toast.present();
  } finally {
    loading.value = false;
  }
}

onUnmounted(() => {
  if (cooldownTimer) {
    clearInterval(cooldownTimer);
  }
});
</script>

<style scoped>
.forgot-content {
  --background: #001829;
}

.forgot-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100%;
  padding: 2rem 1rem;
}

.forgot-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 420px;
  width: 100%;
  overflow: hidden;
}

.forgot-header {
  padding: 30px 30px 20px;
  text-align: center;
  border-bottom: 1px solid #f0f0f0;
}

.icon-circle {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px;
}

.lock-icon {
  font-size: 30px;
  color: #fff;
}

.forgot-header h2 {
  color: #2d8659;
  margin: 0 0 10px;
  font-size: 20px;
  font-weight: 600;
}

.forgot-header p {
  color: #6c757d;
  font-size: 13px;
  margin: 0;
}

.forgot-body {
  padding: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.email-group {
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
  border-color: #2d8659;
  box-shadow: 0 0 0 0.2rem rgba(45, 134, 89, 0.15);
}

.otp-input {
  text-align: center;
  font-size: 28px;
  letter-spacing: 8px;
}

.error-note {
  font-size: 12px;
  padding-left: 18px;
}

.helper-note {
  font-size: 11px;
  padding-left: 18px;
  margin-top: 5px;
}

.btn-action {
  --background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  --color: white;
  --border-radius: 12px;
  --padding-top: 14px;
  --padding-bottom: 14px;
  margin-top: 10px;
  font-weight: 600;
  height: 48px;
}

.btn-action:disabled {
  --background: #6c757d;
  opacity: 0.6;
}

.resend-link {
  text-align: center;
  margin-top: 15px;
}

.resend-link ion-button {
  --color: #2d8659;
  font-size: 13px;
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

.links {
  margin-top: 1.5rem;
  text-align: center;
}

.back-link {
  --color: #2d8659;
  font-size: 14px;
}
</style>