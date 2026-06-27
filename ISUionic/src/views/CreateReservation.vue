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
              :rows="4"
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
              >
                {{ venue.name }} - {{ venue.location }}
                <span v-if="!venue.is_available"> ({{ venue.status === 'damaged' ? 'Damaged' : 'Under Construction' }})</span>
              </ion-select-option>
            </ion-select>
            <ion-note slot="error" v-if="errors.venue_id">{{ errors.venue_id }}</ion-note>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.area_name }">
            <ion-label position="stacked">Area</ion-label>
            <ion-select
              v-model="selectedAreaId"
              :placeholder="form.venue_id ? 'Select an area' : 'Please select a venue first'"
              interface="popover"
              @ionChange="onAreaChange"
              :disabled="!form.venue_id"
            >
              <ion-select-option value="none">None</ion-select-option>
              <ion-select-option
                v-for="area in filteredAreas"
                :key="area.id"
                :value="area.id"
              >
                {{ area.name }}
              </ion-select-option>
              <!-- Show custom area name as a regular option if it exists and doesn't match an existing area -->
              <ion-select-option 
                v-if="form.area_name && !areas.find(a => a.name === form.area_name && a.venue_id === form.venue_id)" 
                :value="'custom-' + form.area_name"
              >
                {{ form.area_name }}
              </ion-select-option>
              <ion-select-option value="others" class="others-option">Others:</ion-select-option>
            </ion-select>
            <ion-note slot="error" v-if="errors.area_name">{{ errors.area_name }}</ion-note>
            <ion-note slot="helper" v-if="form.venue_id && filteredAreas.length === 0 && !areasLoading" color="medium">
              No areas available for this venue
            </ion-note>
          </ion-item>

          <!-- Custom Area Input (shown when "Others:" is selected) -->
          <ion-item v-if="selectedAreaId === 'others'">
            <ion-label position="stacked">Enter Custom Area Name</ion-label>
            <ion-input
              v-model="form.area_name"
              placeholder="Enter area name (e.g., Room 101, Lab 2)"
              @ion-input="clearError('area_name')"
            ></ion-input>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.date }">
            <ion-label position="stacked">
              <ion-icon :icon="calendarOutline" style="margin-right: 4px;"></ion-icon>
              Event Date <span class="required">*</span>
            </ion-label>
            <input
              type="date"
              v-model="form.date"
              class="native-datetime-input"
              :min="minDateStr"
            />
            <div v-if="errors.date" style="color: var(--ion-color-danger); font-size: 0.85rem; margin-top: 0.5rem;">
              {{ errors.date }}
            </div>
            <ion-note slot="helper" color="medium">
              Reservations must be made at least 7 days in advance
            </ion-note>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.start_time }">
            <ion-label position="stacked">
              <ion-icon :icon="timeOutline" style="margin-right: 4px;"></ion-icon>
              Start Time <span class="required">*</span>
            </ion-label>
            <input
              type="time"
              v-model="form.start_time"
              class="native-datetime-input"
              @input="clearError('start_time')"
            />
            <ion-note slot="error" v-if="errors.start_time">{{ errors.start_time }}</ion-note>
          </ion-item>

          <ion-item :class="{ 'ion-invalid': errors.end_time }">
            <ion-label position="stacked">
              <ion-icon :icon="timeOutline" style="margin-right: 4px;"></ion-icon>
              End Time <span class="required">*</span>
            </ion-label>
            <input
              type="time"
              v-model="form.end_time"
              class="native-datetime-input"
              @input="clearError('end_time')"
            />
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
              <span v-if="!validators.required(form.date) || errors.date">Date, </span>
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
              :disabled="forceLoading || hasApprovedConflicts"
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
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
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
import { areasApi } from '../api/areas';
import { reservationsApi } from '../api/reservations';
import { useApi } from '../composables/useApi';
import { validators } from '../utils/validators';
import { useRequireAuth } from '../composables/useRequireAuth';
import { Venue, Area, CreateReservationData, ConflictingReservation } from '../types';
import { API_BASE_URL } from '../config/env';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { requireAuth } = useRequireAuth();
const isEdit = computed(() => !!route.params.id);

const { loading: venuesLoading, execute: executeVenues } = useApi<Venue[]>();
const { loading: areasLoading, execute: executeAreas } = useApi<Area[]>();
const { loading: reservationLoading, execute: executeReservation } = useApi<any>();
const { loading: submitLoading, execute: executeSubmit, showSuccess, error: submitError } = useApi<any>();

const loading = computed(() => venuesLoading.value || areasLoading.value || reservationLoading.value || submitLoading.value);

const venues = ref<Venue[]>([]);
const areas = ref<Area[]>([]);
const selectedAreaId = ref<number | string | null>(null);
const form = ref<CreateReservationData & { date: string; area_name: string }>({
  title: '',
  description: '',
  venue_id: 0,
  area_id: null,
  area_name: '',
  date: '',
  start_time: '',
  end_time: '',
  capacity: 1,
});

const errors = ref<Record<string, string>>({});
const conflicts = ref<ConflictingReservation[]>([]);
const showConflictModal = ref(false);
const forceLoading = ref(false);

// Check if any conflict is with an approved reservation (can't override)
const hasApprovedConflicts = computed(() => {
  return conflicts.value.some(conflict => conflict.status === 'approved');
});

const minDate = new Date();
minDate.setDate(minDate.getDate() + 7);
const minDateStr = minDate.toISOString().split('T')[0];

const isFormValid = computed(() => {
  const hasTitle = validators.required(form.value.title);
  const hasVenue = form.value.venue_id > 0;
  const hasDate = validators.required(form.value.date) && validators.dateNotPast(form.value.date) && validators.dateNotTooSoon(form.value.date);
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

// Watch for date changes and validate in real-time
watch(() => form.value.date, (newDate) => {
  if (newDate && !validators.dateNotTooSoon(newDate)) {
    errors.value.date = 'Date must be at least 7 days in advance';
  } else if (newDate && !validators.dateNotPast(newDate)) {
    errors.value.date = 'Date cannot be in the past';
  } else {
    clearError('date');
  }
});

function onVenueChange() {
  clearError('venue_id');
  // Reset area selection when venue changes
  selectedAreaId.value = null;
  form.value.area_name = '';
}

function onAreaChange() {
  if (selectedAreaId.value === 'others') {
    // User selected "Others:" - clear area_name to allow custom input
    form.value.area_name = '';
  } else if (selectedAreaId.value && typeof selectedAreaId.value === 'number') {
    // User selected an existing area - get the area name
    const selectedArea = areas.value.find(a => a.id === selectedAreaId.value);
    form.value.area_name = selectedArea?.name || '';
  } else if (typeof selectedAreaId.value === 'string' && selectedAreaId.value.startsWith('custom-')) {
    // User selected a custom area name from the dropdown
    form.value.area_name = selectedAreaId.value.replace('custom-', '');
  } else {
    // User selected "None" or cleared selection
    form.value.area_name = '';
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

async function loadAreas() {
  const data = await executeAreas(() => areasApi.getAll());
  if (data) {
    areas.value = data;
    console.log('✅ Areas loaded:', areas.value);
    console.log('✅ Areas count:', areas.value.length);
    if (areas.value.length > 0) {
      console.log('✅ First area:', areas.value[0]);
    }
  } else {
    console.error('❌ Failed to load areas');
  }
}

// Filter areas based on selected venue
const filteredAreas = computed(() => {
  console.log('🔍 Filtering areas...');
  console.log('  form.venue_id:', form.value.venue_id, 'type:', typeof form.value.venue_id);
  
  if (!form.value.venue_id) {
    console.log('  ❌ No venue selected, returning empty array');
    return [];
  }
  
  // Convert venue_id to number to ensure type matching
  const venueIdNum = typeof form.value.venue_id === 'string' 
    ? parseInt(form.value.venue_id) 
    : form.value.venue_id;
  
  console.log('  venueIdNum:', venueIdNum, 'type:', typeof venueIdNum);
  console.log('  Total areas:', areas.value.length);
  
  const filtered = areas.value.filter(a => {
    const match = a.venue_id === venueIdNum;
    console.log('  Area:', a.name, 'venue_id:', a.venue_id, 'match:', match);
    return match;
  });
  
  console.log('  ✅ Filtered areas:', filtered);
  return filtered;
});

async function loadReservation() {
  if (!isEdit.value || !route.params.id || isNaN(Number(route.params.id))) return;
  
  const id = parseInt(route.params.id as string);
  const data = await executeReservation(() => reservationsApi.getById(id));
  if (data) {
    // Authorization checks for editing:
    // 1. Only the owner can edit their reservation
    // 2. Only pending reservations can be edited
    if (data.user_id !== authStore.user?.id) {
      const toast = await toastController.create({
        message: 'You are not authorized to edit this reservation. You can only edit your own reservations.',
        duration: 5000,
        position: 'top',
        color: 'danger',
        buttons: [{ text: 'OK', role: 'cancel' }]
      });
      await toast.present();
      router.push('/reservations');
      return;
    }

    if (data.status !== 'pending') {
      const toast = await toastController.create({
        message: 'This reservation cannot be edited because its status is "' + data.status + '". Only pending reservations can be edited.',
        duration: 5000,
        position: 'top',
        color: 'warning',
        buttons: [{ text: 'OK', role: 'cancel' }]
      });
      await toast.present();
      router.push('/reservations');
      return;
    }

    // Ensure times are in H:i format (trim seconds if present)
    const normalizeTime = (time: string) => time ? time.substring(0, 5) : '';

    form.value = {
      title: data.title,
      description: data.description || '',
      venue_id: data.venue_id,
      area_id: data.area_id || null,
      area_name: data.area_name || '',
      date: data.date,
      start_time: normalizeTime(data.start_time),
      end_time: normalizeTime(data.end_time),
      capacity: data.capacity,
    };

    // Pre-select the area if the reservation has one
    if (data.area_id) {
      // Existing area linked in the areas table
      selectedAreaId.value = data.area_id;
    } else if (data.area_name) {
      // Check if the area_name matches an existing area in the areas table
      const matchingArea = areas.value.find(a => 
        a.name.toLowerCase() === data.area_name.toLowerCase() && 
        a.venue_id === data.venue_id
      );
      
      if (matchingArea) {
        // Area exists in the table, select it by ID
        selectedAreaId.value = matchingArea.id;
        form.value.area_name = matchingArea.name;
      } else {
        // Custom area name (not in the areas table) - select it as custom
        selectedAreaId.value = 'custom-' + data.area_name;
        form.value.area_name = data.area_name;
      }
    } else {
      // No area selected
      selectedAreaId.value = null;
    }
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

  // Area is optional, no validation needed

  if (!validators.required(form.value.date)) {
    errors.value.date = 'Date is required';
  } else if (!validators.dateNotPast(form.value.date)) {
    errors.value.date = 'Date cannot be in the past';
  } else if (!validators.dateNotTooSoon(form.value.date)) {
    errors.value.date = 'Date must be at least 7 days in advance';
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
    area_id: selectedAreaId.value && typeof selectedAreaId.value === 'number' ? selectedAreaId.value : undefined,
    area_name: (selectedAreaId.value === 'others' || (typeof selectedAreaId.value === 'string' && selectedAreaId.value.startsWith('custom-'))) ? form.value.area_name || undefined : undefined,
    date: form.value.date,
    start_time: form.value.start_time,
    end_time: form.value.end_time,
    capacity: form.value.capacity,
  };

  if (isEdit.value) {
    const id = parseInt(route.params.id as string);
    try {
      const response = await reservationsApi.update(id, submitData);
      
      if (response) {
        showSuccess('Reservation updated successfully');
        router.push('/reservations');
      }
    } catch (err: any) {
      // Check if the error response contains conflicts (409 status)
      if (err.response?.status === 409 && err.response?.data?.conflicts) {
        conflicts.value = err.response.data.conflicts;
        showConflictModal.value = true;
        return;
      }
      
      // For other errors, show error toast
      const errorMessage = err.response?.data?.message || err.message || 'Failed to update reservation';
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
    area_id: selectedAreaId.value && typeof selectedAreaId.value === 'number' ? selectedAreaId.value : undefined,
    area_name: (selectedAreaId.value === 'others' || (typeof selectedAreaId.value === 'string' && selectedAreaId.value.startsWith('custom-'))) ? form.value.area_name || undefined : undefined,
    date: form.value.date,
    start_time: form.value.start_time,
    end_time: form.value.end_time,
    capacity: form.value.capacity,
  };

  try {
    if (isEdit.value) {
      // For edit: update the reservation with force flag
      const id = parseInt(route.params.id as string);
      const response = await reservationsApi.update(id, { ...submitData, force: true });
      
      if (response) {
        showSuccess('Reservation updated successfully. It is now pending and will be reviewed by an administrator.');
        showConflictModal.value = false;
        router.push('/reservations');
      }
    } else {
      // For create: create with force flag
      const response = await reservationsApi.create(submitData, true);
      
      if (response.data) {
        showSuccess('Reservation created successfully. It is now pending and will be reviewed by an administrator.');
        showConflictModal.value = false;
        router.push('/reservations');
      }
    }
  } catch (err: any) {
    const errorMessage = err.response?.data?.message || err.message || (isEdit.value ? 'Failed to update reservation' : 'Failed to create reservation');
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
  await loadAreas();
  
  // Load reservation AFTER areas are loaded so we can match area names
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

/* Native datetime input styling - like Laravel edit form */
.native-datetime-input {
  width: 100%;
  height: 2.75rem;
  border-radius: 8px;
  border: 1px solid var(--ion-color-light-shade, #ccc);
  background: var(--ion-color-light, #f4f5f8);
  font-size: 0.95rem;
  padding: 0 0.75rem;
  color: var(--ion-color-dark, #222);
  font-family: inherit;
  box-sizing: border-box;
}

.native-datetime-input:focus {
  border-color: var(--ion-color-primary, #3880ff);
  outline: none;
}

.native-datetime-input::-webkit-calendar-picker-indicator {
  cursor: pointer;
  opacity: 0.6;
  padding: 0.25rem;
}

.native-datetime-input::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
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