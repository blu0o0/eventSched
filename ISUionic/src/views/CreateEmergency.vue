<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button></ion-back-button>
        </ion-buttons>
        <ion-title>Report Emergency</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <div class="form-container">
        <form @submit.prevent="handleSubmit">
          <ion-item :class="{ 'ion-invalid': errors.type }">
            <ion-label position="stacked">Emergency Type <span class="required">*</span></ion-label>
            <ion-select
              v-model="form.type"
              placeholder="Select emergency type"
              interface="popover"
              @ionChange="(event) => { form.type = event.detail.value; clearError('type'); }"
            >
              <ion-select-option value="Fire">Fire</ion-select-option>
              <ion-select-option value="Medical">Medical Emergency</ion-select-option>
              <ion-select-option value="Security">Security Issue</ion-select-option>
              <ion-select-option value="Structural">Structural Damage</ion-select-option>
              <ion-select-option value="Other">Other</ion-select-option>
            </ion-select>
            <ion-note slot="error" v-if="errors.type">{{ errors.type }}</ion-note>
          </ion-item>

          <ion-item>
            <ion-label position="stacked">Or enter custom type</ion-label>
            <ion-input
              v-model="customType"
              placeholder="Enter emergency type"
              @ion-input="onCustomTypeInput"
            ></ion-input>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.description }">
            <ion-label position="stacked">Description <span class="required">*</span></ion-label>
            <ion-textarea
              v-model="form.description"
              placeholder="Provide detailed description of the emergency..."
              rows="6"
              @ion-input="clearError('description')"
            ></ion-textarea>
            <ion-note slot="error" v-if="errors.description">{{ errors.description }}</ion-note>
          </ion-item>

          <div class="form-actions">
            <!-- Debug info (remove in production) -->
            <ion-note v-if="!isFormValid && !loading" color="warning" style="display: block; margin-bottom: 1rem; font-size: 0.8rem;">
              Please fill in all required fields: 
              <span v-if="!validators.required(form.type)">Emergency Type, </span>
              <span v-if="!validators.required(form.description)">Description, </span>
              <span v-if="validators.required(form.description) && !validators.minLength(form.description, 10)">Description must be at least 10 characters</span>
            </ion-note>
            
            <ion-button expand="block" type="submit" :disabled="loading || !isFormValid">
              <ion-spinner v-if="loading" name="crescent"></ion-spinner>
              <span v-else>Submit Report</span>
            </ion-button>
          </div>
        </form>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonBackButton,
  IonButtons,
  IonItem,
  IonLabel,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  IonButton,
  IonNote,
  IonSpinner,
  toastController,
} from '@ionic/vue';
import { emergencyApi } from '../api/emergency';
import { useApi } from '../composables/useApi';
import { validators } from '../utils/validators';
import { CreateEmergencyData } from '../types';

const router = useRouter();
const { loading, execute, showSuccess } = useApi<any>();

const form = ref<CreateEmergencyData>({
  type: '',
  description: '',
});

const customType = ref('');

const errors = ref<Record<string, string>>({});

const isFormValid = computed(() => {
  return (
    validators.required(form.value.type) &&
    validators.required(form.value.description) &&
    validators.minLength(form.value.description, 10)
  );
});

function clearError(field: string) {
  if (errors.value[field]) {
    delete errors.value[field];
  }
}

function onCustomTypeInput() {
  if (customType.value) {
    form.value.type = customType.value;
    clearError('type');
  }
}

function validateForm(): boolean {
  errors.value = {};

  if (!validators.required(form.value.type)) {
    errors.value.type = 'Emergency type is required';
  }

  if (!validators.required(form.value.description)) {
    errors.value.description = 'Description is required';
  } else if (!validators.minLength(form.value.description, 10)) {
    errors.value.description = 'Description must be at least 10 characters';
  }

  return Object.keys(errors.value).length === 0;
}

async function handleSubmit() {
  if (!validateForm()) {
    return;
  }

  try {
    await execute(() => emergencyApi.create(form.value));
    showSuccess('Emergency report submitted successfully');
    router.push('/emergency');
  } catch (error: any) {
    const errorMessage =
      error.response?.data?.message || error.message || 'Failed to submit emergency report';
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
.form-container {
  padding: 1rem;
}

.required {
  color: var(--ion-color-danger);
}

ion-item {
  margin-bottom: 1rem;
}

.form-actions {
  margin-top: 2rem;
  padding: 0 1rem;
}
</style>

