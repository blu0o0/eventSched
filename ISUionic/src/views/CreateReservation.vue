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

    <!-- Conflict Modal -->
    <ion-modal
      :is-open="showConflictModal"
      @didDismiss="showConflictModal = false"
      :backdrop-dismiss="false"
      class="conflict-modal"
    >
      <div class="conflict-modal-wrapper">
        <ion-header class="conflict-header">
          <ion-toolbar class="conflict-toolbar">
            <ion-buttons slot="start">
              <ion-button @click="goBackHome" class="conflict-home-btn">
                <ion-icon :icon="homeOutline" slot="icon-only"></ion-icon>
              </ion-button>
            </ion-buttons>
            <ion-title>
              Reservation Conflict Detected
            </ion-title>
          </ion-toolbar>
        </ion-header>
        <ion-content class="ion-padding">
          <div class="conflict-modal-content">
            <div v-for="conflict in conflicts" :key="conflict.id" class="conflict-card-item">
              <ion-card class="conflict-card">
                <ion-card-header>
                  <ion-card-title>{{ conflict.title }}</ion-card-title>
                  <ion-card-subtitle>
                    <ion-icon :icon="personOutline" /> {{ conflict.user_name }}
                  </ion-card-subtitle>
                </ion-card-header>
                <ion-card-content>
                  <div class="conflict-info">
                    <div class="info-item">
                      <ion-icon :icon="calendarOutline" />
                      <span>{{ formatDate(conflict.date) }}</span>
                    </div>
                    <div class="info-item">
                      <ion-icon :icon="timeOutline" />
                      <span>{{ formatTime(conflict.start_time) }} - {{ formatTime(conflict.end_time) }}</span>
                    </div>
                    <div class="info-item">
                      <ion-icon :icon="locationOutline" />
                      <span>{{ conflict.venue_name }}</span>
                    </div>
                    <div class="info-item" v-if="conflict.description">
                      <ion-icon :icon="documentTextOutline" />
                      <span>{{ truncateText(conflict.description, 80) }}</span>
                    </div>
                  </div>
                </ion-card-content>
              </ion-card>
            </div>
          </div>

          <div class="conflict-actions">
            <ion-button
              expand="block"
              color="medium"
              fill="outline"
              @click="keepEditing"
              class="keep-editing-btn"
            >
              Keep Editing
            </ion-button>

            <ion-button
              expand="block"
              color="primary"
              @click="keepReservationAnyway"
              :disabled="forceLoading"
            >
              <ion-spinner v-if="forceLoading" name="crescent"></ion-spinner>
              <span v-else>Keep Reservation Anyway</span>
            </ion-button>
          </div>
        </ion-content>
      </div>
    </ion-modal>
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
import {
  personOutline,
  calendarOutline,
  timeOutline,
  locationOutline,
  documentTextOutline,
  checkmarkCircleOutline,
  createOutline,
  homeOutline,
} from 'ionicons/icons';
import { venuesApi } from '../api/venues';
import { reservationsApi } from '../api/reservations';
import { useApi } from '../composables/useApi';
import { validators } from '../utils/validators';
import { useRequireAuth } from '../composables/useRequireAuth';
import { Venue, CreateReservationData, ConflictingReservation } from '../types';
import { API_BASE_URL } from '../config/env';

const route = useRoute();
const router = useRouter();
const { requireAuth } = useRequireAuth();
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
const conflicts = ref<ConflictingReservation[]>([]);
const showConflictModal = ref(false);
const forceLoading = ref(false);

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
}

function onVenueChange() {
  clearError('venue_id');
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
  }
}

function formatTime(timeString: string): string {
  if (!timeString) return '';
  const [hours, minutes] = timeString.split(':');
  const h = parseInt(hours, 10);
  const ampm = h >= 12 ? 'PM' : 'AM';
  const hour12 = h % 12 || 12;
  return `${hour12}:${minutes} ${ampm}`;
}

function formatDate(dateString: string): string {
  const date = new Date(dateString + 'T00:00:00');
  return date.toLocaleDateString('en-US', { 
    weekday: 'short', 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
}

function truncateText(text: string, maxLength: number): string {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
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
  if (!isEdit.value || !route.params.id || isNaN(Number(route.params.id))) return;
  
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
    
    if (result === null) {
      return;
    }

    // Success - show success message and navigate
    showSuccess('Reservation updated successfully');
    router.push('/reservations');
  } else {
    try {
      const response = await reservationsApi.create(submitData, false);
      
      // Check if response has conflicts (status 409)
      if (response.conflicts && response.conflicts.length > 0) {
        // Show the conflict modal with conflicting reservation details
        conflicts.value = response.conflicts;
        showConflictModal.value = true;
        return;
      }
      
      // Success
      if (response.data) {
        showSuccess('Reservation created successfully');
        router.push('/reservations');
      }
    } catch (err: any) {
      // Check if the error response contains conflicts
      if (err.response?.status === 409 && err.response?.data?.conflicts) {
        conflicts.value = err.response.data.conflicts;
        showConflictModal.value = true;
        return;
      }
      
      // For other errors, useApi's toast will handle it
      const errorMessage = err.response?.data?.message || err.message || 'Failed to create reservation';
      
      // Let useApi handle showing the toast
      const toast = await toastController.create({
        message: errorMessage,
        duration: 5000,
        position: 'top',
        color: 'danger',
        buttons: [{ text: 'OK', role: 'cancel' }]
      });
      await toast.present();
      return;
    }
  }
}

async function keepReservationAnyway() {
  forceLoading.value = true;
  
  const submitData: CreateReservationData = {
    title: form.value.title,
    description: form.value.description || undefined,
    venue_id: form.value.venue_id,
    date: form.value.date,
    start_time: form.value.start_time,
    end_time: form.value.end_time,
    capacity: form.value.capacity,
  };

  try {
    const response = await reservationsApi.create(submitData, true);
    
    if (response.data) {
      showSuccess('Reservation created successfully. It is now pending and will be reviewed by an administrator.');
      showConflictModal.value = false;
      router.push('/reservations');
    }
  } catch (err: any) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to create reservation';
    const toast = await toastController.create({
      message: errorMessage,
      duration: 5000,
      position: 'top',
      color: 'danger',
      buttons: [{ text: 'OK', role: 'cancel' }]
    });
    await toast.present();
  } finally {
    forceLoading.value = false;
  }
}

function keepEditing() {
  showConflictModal.value = false;
}

function goBackHome() {
  showConflictModal.value = false;
  router.push('/home');
}

onMounted(async () => {
  // Check authentication before allowing access to create reservation
  const hasAccess = await requireAuth('You must be logged in to create a reservation.');
  if (!hasAccess) {
    // User was redirected to login, stop loading
    return;
  }
  
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

/* Conflict Modal Styles */

.conflict-header {
  background: transparent;
}

.conflict-toolbar {
  --background: linear-gradient(135deg, #d32f2f 0%, #9f0505 50%, rgb(128, 6, 6)100%);
  --color: white;
}

.conflict-toolbar ion-title {
  color: white;
  font-weight: 600;
  font-size: 1.1rem;
  text-align: center;
  padding: 0 16px;
}

.conflict-home-btn {
  --background: rgb(179, 3, 3);
  --color: #370909;
  --border-radius: 10%;
  --width: 1em;
  --height: 1em;
  --padding-start: 0;
  --padding-end: 0;
  --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  
}

.conflict-home-btn ion-icon {
  
  font-size: 1rem;
}

.conflict-modal-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.conflict-modal-wrapper ion-content {
  --padding-top: 16px;
  --padding-bottom: 16px;
  --padding-start: 16px;
  --padding-end: 16px;
}

.conflict-modal-content {
  padding: 0;
}

.conflict-card-item {
  margin-bottom: 0.75rem;
}

.conflict-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-left: 4px solid var(--ion-color-danger);
  margin: 0;
}

.conflict-card ion-card-header {
  padding: 0.75rem 1rem;
  background: rgba(255, 0, 0, 0.03);
}

.conflict-card ion-card-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.conflict-card ion-card-subtitle {
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 0.25rem;
}

.conflict-card ion-card-content {
  padding: 0.75rem 1rem;
}

.conflict-info {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.conflict-info .info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--ion-color-medium);
}

.conflict-info .info-item ion-icon {
  font-size: 1rem;
  color: var(--ion-color-primary);
  min-width: 1rem;
}

.conflict-question {
  font-size: 1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
  margin: 1.5rem 0 1rem 0;
  text-align: center;
}

.conflict-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding-bottom: 1rem;
}

.conflict-actions ion-button {
  margin: 0;
}

.conflict-actions ion-button ion-icon {
  margin-right: 0.4rem;
}

.conflict-note {
  font-size: 0.8rem;
  color: var(--ion-color-medium);
  text-align: center;
  margin: 0 0.5rem;
  line-height: 1.4;
  font-style: italic;
}

.keep-editing-btn {
  --border-color: var(--ion-color-medium);
  --color: var(--ion-color-medium);
}

.go-home-btn {
  --color: var(--ion-color-dark);
}
</style>

<style>
/* Global styles for conflict modal - not scoped so CSS custom properties work on ion-modal */
.conflict-modal {
  --width: 90%;
  --height: 80%;
  --max-width: 500px;
  --border-radius: 16px;
}
</style>
