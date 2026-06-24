<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button></ion-back-button>
        </ion-buttons>
        <ion-title>{{ isEdit ? 'Edit Reservation' : 'Create Reservation' }}</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <div class="form-container">
        <form @submit.prevent="handleSubmit">
          <ion-item :class="{ 'ion-invalid': errors.title }">
            <ion-label position="stacked">Title <span class="required">*</span></ion-label>
            <ion-input
              v-model="form.title"
              placeholder="Enter reservation title"
              @ion-input="clearError('title')"
            ></ion-input>
            <ion-note slot="error" v-if="errors.title">{{ errors.title }}</ion-note>
          </ion-item>

          <ion-item>
            <ion-label position="stacked">Description</ion-label>
            <ion-textarea
              v-model="form.description"
              placeholder="Enter description (optional)"
              rows="4"
            ></ion-textarea>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.venue_id }">
            <ion-label position="stacked">Venue <span class="required">*</span></ion-label>
            <ion-select
              v-model="form.venue_id"
              placeholder="Select a venue"
              interface="popover"
              @ionChange="onVenueChange"
            >
              <ion-select-option
                v-for="venue in venues"
                :key="venue.id"
                :value="venue.id"
                :disabled="!venue.is_available && isEdit"
              >
                {{ venue.name }} - {{ venue.location }}
                <span v-if="!venue.is_available"> ({{ venue.status === 'damaged' ? 'Damaged' : 'Under Construction' }})</span>
              </ion-select-option>
            </ion-select>
            <ion-note slot="error" v-if="errors.venue_id">{{ errors.venue_id }}</ion-note>
          </ion-item>

          <!-- Venue Image Preview -->
          <div v-if="selectedVenue && selectedVenueImageUrl" class="venue-image-preview">
            <ion-card>
              <img :src="selectedVenueImageUrl" :alt="selectedVenue.name" @error="handleImageError" />
              <ion-card-header>
                <ion-card-title>{{ selectedVenue.name }}</ion-card-title>
                <ion-card-subtitle>{{ selectedVenue.location }}</ion-card-subtitle>
              </ion-card-header>
              <ion-card-content>
                <p v-if="selectedVenue.description">{{ selectedVenue.description }}</p>
                <p><strong>Max Occupancy:</strong> {{ selectedVenue.capacity }} people</p>
              </ion-card-content>
            </ion-card>
          </div>

          <ion-item :class="{ 'ion-invalid': errors.date }">
            <ion-label position="stacked">Date <span class="required">*</span></ion-label>
            <ion-datetime-button datetime="date"></ion-datetime-button>
            <ion-modal :keep-contents-mounted="true">
              <ion-datetime
                id="date"
                presentation="date"
                :min="minDate"
                :value="form.date || undefined"
                @ionChange="onDateChange"
              ></ion-datetime>
            </ion-modal>
            <ion-note slot="error" v-if="errors.date">{{ errors.date }}</ion-note>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.start_time }">
            <ion-label position="stacked">Start Time <span class="required">*</span></ion-label>
            <ion-datetime-button datetime="start-time"></ion-datetime-button>
            <ion-modal :keep-contents-mounted="true">
              <ion-datetime
                id="start-time"
                presentation="time"
                :value="form.start_time || undefined"
                @ionChange="onStartTimeChange"
              ></ion-datetime>
            </ion-modal>
            <ion-note slot="error" v-if="errors.start_time">{{ errors.start_time }}</ion-note>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.end_time }">
            <ion-label position="stacked">End Time <span class="required">*</span></ion-label>
            <ion-datetime-button datetime="end-time"></ion-datetime-button>
            <ion-modal :keep-contents-mounted="true">
              <ion-datetime
                id="end-time"
                presentation="time"
                :value="form.end_time || undefined"
                @ionChange="onEndTimeChange"
              ></ion-datetime>
            </ion-modal>
            <ion-note slot="error" v-if="errors.end_time">{{ errors.end_time }}</ion-note>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.capacity }">
            <ion-label position="stacked">Expected Max Occupancy <span class="required">*</span></ion-label>
            <ion-input
              v-model.number="form.capacity"
              type="number"
              placeholder="Enter expected max occupancy"
              min="1"
              @ion-input="clearError('capacity')"
            ></ion-input>
            <ion-note slot="error" v-if="errors.capacity">{{ errors.capacity }}</ion-note>
          </ion-item>

          <!-- Overlap Error Alert -->
          <ion-card v-if="overlapError" color="danger" class="overlap-error-card">
            <ion-card-header>
              <ion-card-title>
                <ion-icon :icon="warningOutline" style="margin-right: 0.5rem;"></ion-icon>
                Reservation Conflict
              </ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <p>{{ overlapError }}</p>
              <p style="margin-top: 0.5rem; font-size: 0.9rem; opacity: 0.9;">
                Please choose a different date, time, or venue to avoid conflicts.
              </p>
            </ion-card-content>
          </ion-card>

          <div class="form-actions">
            <!-- Debug info (remove in production) -->
            <ion-note v-if="!isFormValid && !loading" color="warning" style="display: block; margin-bottom: 1rem; font-size: 0.8rem;">
              Please fill in all required fields: 
              <span v-if="!validators.required(form.title)">Title, </span>
              <span v-if="form.venue_id <= 0">Venue, </span>
              <span v-if="!validators.required(form.date)">Date, </span>
              <span v-if="!validators.required(form.start_time)">Start Time, </span>
              <span v-if="!validators.required(form.end_time)">End Time, </span>
              <span v-if="!validators.required(form.capacity) || !validators.positiveNumber(form.capacity)">Max Occupancy</span>
            </ion-note>
            
            <ion-button
              expand="block"
              type="submit"
              :disabled="loading || !isFormValid"
            >
              <ion-spinner v-if="loading" name="crescent"></ion-spinner>
              <span v-else>{{ isEdit ? 'Update' : 'Create' }} Reservation</span>
            </ion-button>
          </div>
        </form>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
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
  IonDatetimeButton,
  IonModal,
  IonDatetime,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardSubtitle,
  IonCardContent,
  IonIcon,
  toastController,
} from '@ionic/vue';
import { warningOutline } from 'ionicons/icons';
import { venuesApi } from '../api/venues';
import { reservationsApi } from '../api/reservations';
import { useApi } from '../composables/useApi';
import { validators } from '../utils/validators';
import { Venue, CreateReservationData } from '../types';
import { API_BASE_URL } from '../config/env';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const { loading: venuesLoading, execute: executeVenues } = useApi<Venue[]>();
const { loading: reservationLoading, execute: executeReservation } = useApi<any>();
const { loading: submitLoading, execute: executeSubmit, showSuccess, error: submitError } = useApi<any>();

const loading = computed(() => venuesLoading.value || reservationLoading.value || submitLoading.value);

const venues = ref<Venue[]>([]);
const form = ref<CreateReservationData & { date: string }>({
  title: '',
  description: '',
  venue_id: 0,
  date: '',
  start_time: '',
  end_time: '',
  capacity: 1,
});

const errors = ref<Record<string, string>>({});
const overlapError = ref<string>('');

const minDate = new Date().toISOString().split('T')[0];

// Get selected venue for image preview
const selectedVenue = computed(() => {
  if (!form.value.venue_id) return null;
  return venues.value.find(v => v.id === form.value.venue_id) || null;
});

// Get full image URL
function getImageUrl(photoUrl: string | null | undefined): string | null {
  if (!photoUrl) return null;
  
  // If already a full URL, return as is
  if (photoUrl.startsWith('http://') || photoUrl.startsWith('https://')) {
    return photoUrl;
  }
  
  // Extract base URL from API_BASE_URL (remove /api)
  // API_BASE_URL format: http://localhost:8000/api or https://domain.com/api
  const baseUrl = API_BASE_URL.replace('/api', '').replace(/\/$/, '');
  
  // If photoUrl starts with /, use it directly, otherwise prepend /
  const path = photoUrl.startsWith('/') ? photoUrl : `/${photoUrl}`;
  
  const fullUrl = `${baseUrl}${path}`;
  console.log('Image URL:', { photoUrl, baseUrl, path, fullUrl });
  return fullUrl;
}

// Computed property for selected venue image URL
const selectedVenueImageUrl = computed(() => {
  if (!selectedVenue.value?.photo_url) return null;
  return getImageUrl(selectedVenue.value.photo_url);
});

const isFormValid = computed(() => {
  const hasTitle = validators.required(form.value.title);
  const hasVenue = form.value.venue_id > 0;
  const hasDate = validators.required(form.value.date) && validators.dateNotPast(form.value.date);
  const hasStartTime = validators.required(form.value.start_time);
  const hasEndTime = validators.required(form.value.end_time);
  const timesValid = hasStartTime && hasEndTime && validators.timeAfter(form.value.start_time, form.value.end_time);
  const hasCapacity = validators.required(form.value.capacity) && validators.positiveNumber(form.value.capacity);
  
  return hasTitle && hasVenue && hasDate && timesValid && hasCapacity;
});

function clearError(field: string) {
  if (errors.value[field]) {
    delete errors.value[field];
  }
  // Clear overlap error when user changes date, time, or venue
  if (field === 'date' || field === 'start_time' || field === 'end_time' || field === 'venue_id') {
    overlapError.value = '';
  }
}

function onVenueChange() {
  clearError('venue_id');
  overlapError.value = '';
}

function handleImageError(event: Event) {
  // Hide image on error
  const img = event.target as HTMLImageElement;
  img.style.display = 'none';
  console.error('Failed to load venue image:', selectedVenue.value?.photo_url);
}

function onDateChange(event: CustomEvent) {
  const dateValue = event.detail.value;
  if (dateValue) {
    // Handle both ISO string and date string formats
    const dateStr = typeof dateValue === 'string' ? dateValue : dateValue.toISOString();
    form.value.date = dateStr.split('T')[0];
    clearError('date');
    overlapError.value = ''; // Clear overlap error when date changes
  }
}

function onStartTimeChange(event: CustomEvent) {
  const timeValue = event.detail.value;
  if (timeValue) {
    // Handle ISO datetime string (e.g., "2024-01-01T14:30:00" or "14:30:00")
    let timeString = '';
    if (typeof timeValue === 'string') {
      if (timeValue.includes('T')) {
        timeString = timeValue.split('T')[1];
      } else {
        timeString = timeValue;
      }
    } else {
      // If it's a Date object, convert to ISO and extract time
      timeString = timeValue.toISOString().split('T')[1];
    }
    // Extract HH:mm format
    form.value.start_time = timeString.substring(0, 5);
    clearError('start_time');
    overlapError.value = ''; // Clear overlap error when time changes
  }
}

function onEndTimeChange(event: CustomEvent) {
  const timeValue = event.detail.value;
  if (timeValue) {
    // Handle ISO datetime string (e.g., "2024-01-01T14:30:00" or "14:30:00")
    let timeString = '';
    if (typeof timeValue === 'string') {
      if (timeValue.includes('T')) {
        timeString = timeValue.split('T')[1];
      } else {
        timeString = timeValue;
      }
    } else {
      // If it's a Date object, convert to ISO and extract time
      timeString = timeValue.toISOString().split('T')[1];
    }
    // Extract HH:mm format
    form.value.end_time = timeString.substring(0, 5);
    clearError('end_time');
    overlapError.value = ''; // Clear overlap error when time changes
  }
}

async function loadVenues() {
  const data = await executeVenues(() => venuesApi.getAll());
  if (data) {
    // Filter to only show Santiago Campus venues and available venues when creating new reservation
    // When editing, show all venues (including the current one even if unavailable)
    if (isEdit.value) {
      venues.value = data.filter(v => v.location === 'Santiago Campus');
    } else {
      venues.value = data.filter(v => v.is_available && v.location === 'Santiago Campus');
    }
    
    // If venue_id is in query params, set it
    const venueId = route.query.venue_id;
    if (venueId && !isEdit.value) {
      form.value.venue_id = parseInt(venueId as string);
    }
  }
}

async function loadReservation() {
  if (!isEdit.value) return;
  
  const id = parseInt(route.params.id as string);
  const data = await executeReservation(() => reservationsApi.getById(id));
  if (data) {
    form.value = {
      title: data.title,
      description: data.description || '',
      venue_id: data.venue_id,
      date: data.date,
      start_time: data.start_time,
      end_time: data.end_time,
      capacity: data.capacity,
    };
  }
}

function validateForm(): boolean {
  errors.value = {};

  if (!validators.required(form.value.title)) {
    errors.value.title = 'Title is required';
  }

  if (!form.value.venue_id || form.value.venue_id <= 0) {
    errors.value.venue_id = 'Please select a venue';
  }

  if (!validators.required(form.value.date)) {
    errors.value.date = 'Date is required';
  } else if (!validators.dateNotPast(form.value.date)) {
    errors.value.date = 'Date cannot be in the past';
  }

  if (!validators.required(form.value.start_time)) {
    errors.value.start_time = 'Start time is required';
  }

  if (!validators.required(form.value.end_time)) {
    errors.value.end_time = 'End time is required';
  } else if (!validators.timeAfter(form.value.start_time, form.value.end_time)) {
    errors.value.end_time = 'End time must be after start time';
  }

  if (!validators.required(form.value.capacity)) {
    errors.value.capacity = 'Max Occupancy is required';
  } else if (!validators.positiveNumber(form.value.capacity)) {
    errors.value.capacity = 'Max Occupancy must be greater than 0';
  }

  return Object.keys(errors.value).length === 0;
}

async function handleSubmit() {
  if (!validateForm()) {
    return;
  }

  // Clear previous overlap error
  overlapError.value = '';

  const submitData: CreateReservationData = {
    title: form.value.title,
    description: form.value.description || undefined,
    venue_id: form.value.venue_id,
    date: form.value.date,
    start_time: form.value.start_time,
    end_time: form.value.end_time,
    capacity: form.value.capacity,
  };

  let result;
  if (isEdit.value) {
    const id = parseInt(route.params.id as string);
    result = await executeSubmit(() => reservationsApi.update(id, submitData));
  } else {
    result = await executeSubmit(() => reservationsApi.create(submitData));
  }

  // Check if the request failed (useApi returns null on error)
  if (result === null) {
    // Get error message from useApi error ref or from the API response
    const errorMessage = submitError.value || 'Failed to save reservation';
    
    // Check if error message contains overlap/conflict information
    if (errorMessage.toLowerCase().includes('overlap') || 
        errorMessage.toLowerCase().includes('conflict') ||
        errorMessage.toLowerCase().includes('reservation')) {
      overlapError.value = errorMessage;
      
      // Also show toast for visibility (useApi already shows one, but we show another with longer duration)
      const toast = await toastController.create({
        message: errorMessage,
        duration: 5000,
        position: 'top',
        color: 'danger',
        buttons: [
          {
            text: 'OK',
            role: 'cancel'
          }
        ]
      });
      await toast.present();
    }
    // Note: useApi already shows a toast, so we don't need to show another for non-overlap errors
    return;
  }

  // Success - show success message and navigate
  if (isEdit.value) {
    showSuccess('Reservation updated successfully');
  } else {
    showSuccess('Reservation created successfully');
  }
  
  router.push('/reservations');
}

onMounted(async () => {
  await loadVenues();
  if (isEdit.value) {
    await loadReservation();
  }
});
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

.overlap-error-card {
  margin: 1rem 0;
  animation: slideIn 0.3s ease-out;
}

.overlap-error-card ion-card-title {
  display: flex;
  align-items: center;
  font-size: 1.1rem;
}

.overlap-error-card ion-card-content p {
  margin: 0;
  line-height: 1.5;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.venue-image-preview {
  margin: 1rem 0;
  animation: slideIn 0.3s ease-out;
}

.venue-image-preview ion-card {
  margin: 0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.venue-image-preview img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
}

.venue-image-preview ion-card-header {
  padding: 1rem;
}

.venue-image-preview ion-card-title {
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.venue-image-preview ion-card-subtitle {
  font-size: 0.9rem;
  color: var(--ion-color-medium);
}

.venue-image-preview ion-card-content {
  padding: 0 1rem 1rem 1rem;
}

.venue-image-preview ion-card-content p {
  margin: 0.5rem 0;
  font-size: 0.9rem;
  line-height: 1.5;
  color: var(--ion-color-dark);
}

.venue-image-preview ion-card-content p:last-child {
  margin-top: 0.75rem;
  font-weight: 500;
}
</style>

