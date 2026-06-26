<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button></ion-back-button>
        </ion-buttons>
        <ion-title>Reservation Details</ion-title>
        <ion-buttons slot="end" v-if="canEdit">
          <ion-button @click="goToEdit">
            <ion-icon :icon="createOutline" slot="icon-only" />
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <LoadingSpinner v-if="loading && !reservation" message="Loading reservation..." />

      <div v-else-if="reservation" class="reservation-detail-container">
        <ion-card>
          <ion-card-header>
            <div class="header-content">
              <ion-card-title>{{ reservation.title }}</ion-card-title>
              <StatusBadge :status="reservation.status" />
            </div>
          </ion-card-header>
          <ion-card-content>
            <div class="detail-section">
              <h3>Venue Information</h3>
              <div class="info-item">
                <ion-icon :icon="locationOutline" />
                <span>{{ reservation.venue.name }}<span v-if="reservation.area_name">, Area: {{ reservation.area_name }}</span></span>
              </div>
              <div class="info-item">
                <span>{{ reservation.venue.location }}</span>
              </div>
              <div class="info-item">
                <ion-icon :icon="peopleOutline" />
                <span>Max Occupancy: {{ reservation.venue.capacity }} people</span>
              </div>
            </div>

            <div class="detail-section">
              <h3>Reservation Details</h3>
              <div class="info-item">
                <ion-icon :icon="calendarOutline" />
                <span>{{ formatDate(reservation.date) }}</span>
              </div>
              <div class="info-item">
                <ion-icon :icon="timeOutline" />
                <span>{{ formatTime(reservation.start_time) }} - {{ formatTime(reservation.end_time) }}</span>
              </div>
              <div class="info-item">
                <ion-icon :icon="peopleOutline" />
                <span>Expected Max Occupancy: {{ reservation.capacity }} people</span>
              </div>
              <div class="info-item" v-if="reservation.area || reservation.area_name">
                <ion-icon :icon="gridOutline" />
                <span>
                  <span v-if="reservation.area">{{ reservation.area.name }}</span>
                  <span v-else>{{ reservation.area_name }}</span>
                </span>
              </div>
              <div v-if="reservation.area && reservation.area.photo_url" class="area-photo">
                <img :src="reservation.area.photo_url" :alt="reservation.area.name" />
              </div>
            </div>

            <div v-if="reservation.description" class="detail-section">
              <h3>Description</h3>
              <p>{{ reservation.description }}</p>
            </div>

            <div class="detail-section">
              <h3>Reserved By</h3>
              <div class="info-item">
                <ion-icon :icon="personOutline" />
                <span>{{ reservation.user.name }}</span>
              </div>
              <div class="info-item">
                <span>{{ reservation.user.email }}</span>
              </div>
            </div>

            <div v-if="reservation.status === 'approved' && reservation.approved_at" class="detail-section">
              <h3>Approval Information</h3>
              <div class="info-item">
                <ion-icon :icon="checkmarkCircleOutline" />
                <span>Approved on {{ formatDateTime(reservation.approved_at) }}</span>
              </div>
            </div>

            <div v-if="reservation.status === 'rejected' && reservation.rejection_reason" class="detail-section">
              <h3>Rejection Reason</h3>
              <ion-item color="danger">
                <ion-label>
                  <p>{{ reservation.rejection_reason }}</p>
                </ion-label>
              </ion-item>
            </div>

            <div v-if="reservation.status === 'postponed' && reservation.postponement_reason" class="detail-section">
              <h3>Postponement Information</h3>
              <ion-item color="warning">
                <ion-label>
                  <p><strong>Reason:</strong> {{ reservation.postponement_reason }}</p>
                  <p v-if="reservation.venue.unavailable_until" class="mt-2">
                    <strong>Venue Available From:</strong> {{ formatDate(reservation.venue.unavailable_until) }}
                  </p>
                </ion-label>
              </ion-item>
            </div>

            <div v-if="reservation.venue.is_unavailable" class="detail-section">
              <h3>Venue Status</h3>
              <ion-item :color="reservation.venue.status === 'damaged' ? 'danger' : 'warning'">
                <ion-label>
                  <p><strong>Status:</strong> 
                    <span v-if="reservation.venue.status === 'damaged'">Damaged</span>
                    <span v-else-if="reservation.venue.status === 'under_construction'">Under Construction</span>
                  </p>
                  <p v-if="reservation.venue.unavailable_until" class="mt-2">
                    <strong>Available From:</strong> {{ formatDate(reservation.venue.unavailable_until) }}
                    <span v-if="reservation.venue.days_until_available !== null">
                      ({{ reservation.venue.days_until_available }} day(s))
                    </span>
                  </p>
                </ion-label>
              </ion-item>
            </div>
          </ion-card-content>
        </ion-card>

        <div class="actions" v-if="canReschedule">
          <ion-button expand="block" color="primary" @click="openRescheduleModal">
            <ion-icon :icon="calendarOutline" slot="start" />
            Reschedule Reservation
          </ion-button>
        </div>

        <div class="actions" v-if="canDelete">
          <ion-button expand="block" color="danger" @click="handleDelete">
            Delete Reservation
          </ion-button>
        </div>
      </div>

      <!-- Reschedule Modal -->
      <ion-modal :is-open="isRescheduleModalOpen" @did-dismiss="closeRescheduleModal">
        <ion-header>
          <ion-toolbar>
            <ion-title>Reschedule Reservation</ion-title>
            <ion-buttons slot="end">
              <ion-button @click="closeRescheduleModal">Close</ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>
        <ion-content class="ion-padding">
          <div v-if="reservation">
            <p class="ion-margin-bottom">
              You can change the venue or reschedule the date. 
              <strong v-if="reservation.venue.unavailable_until">
                The new date must be after {{ formatDate(reservation.venue.unavailable_until) }}.
              </strong>
            </p>

            <ion-item>
              <ion-label position="stacked">Change Venue (Optional)</ion-label>
              <ion-select
                v-model="rescheduleForm.venue_id"
                placeholder="Select a venue"
                interface="popover"
                @ionChange="rescheduleErrors.date = ''"
              >
                <ion-select-option
                  v-for="venue in availableVenues"
                  :key="venue.id"
                  :value="venue.id"
                >
                  {{ venue.name }} - {{ venue.location }} (Max Occupancy: {{ venue.capacity }})
                  <span v-if="!venue.is_available && venue.unavailable_until">
                    (Available after {{ formatDate(venue.unavailable_until) }})
                  </span>
                </ion-select-option>
              </ion-select>
            </ion-item>

            <ion-item>
              <ion-label position="stacked">Change Date (Optional)</ion-label>
              <ion-datetime-button datetime="reschedule-date"></ion-datetime-button>
              <ion-modal :keep-contents-mounted="true">
                <ion-datetime
                  id="reschedule-date"
                  presentation="date"
                  :min="minRescheduleDate"
                  :value="getDateValue(rescheduleForm.date)"
                  @ionChange="(ev: any) => {
                    if (ev.detail.value) {
                      const date = new Date(ev.detail.value);
                      rescheduleForm.date = date.toISOString().split('T')[0];
                      rescheduleErrors.date = '';
                    }
                  }"
                ></ion-datetime>
              </ion-modal>
              <ion-note slot="error" v-if="rescheduleErrors.date" color="danger">
                {{ rescheduleErrors.date }}
              </ion-note>
              <ion-note v-if="rescheduleForm.venue_id === reservation?.venue_id && reservation?.venue.unavailable_until">
                Date must be after {{ formatDate(reservation.venue.unavailable_until) }}
              </ion-note>
            </ion-item>

            <ion-item>
              <ion-label position="stacked">Change Start Time (Optional)</ion-label>
              <ion-datetime-button datetime="reschedule-start-time"></ion-datetime-button>
              <ion-modal :keep-contents-mounted="true">
                <ion-datetime
                  id="reschedule-start-time"
                  presentation="time"
                  :value="getTimeValue(rescheduleForm.start_time, rescheduleForm.date)"
                  @ionChange="(ev: any) => {
                    if (ev.detail.value) {
                      const time = new Date(ev.detail.value);
                      const hours = String(time.getHours()).padStart(2, '0');
                      const minutes = String(time.getMinutes()).padStart(2, '0');
                      rescheduleForm.start_time = `${hours}:${minutes}`;
                    }
                  }"
                ></ion-datetime>
              </ion-modal>
            </ion-item>

            <ion-item>
              <ion-label position="stacked">Change End Time (Optional)</ion-label>
              <ion-datetime-button datetime="reschedule-end-time"></ion-datetime-button>
              <ion-modal :keep-contents-mounted="true">
                <ion-datetime
                  id="reschedule-end-time"
                  presentation="time"
                  :value="getTimeValue(rescheduleForm.end_time, rescheduleForm.date)"
                  @ionChange="(ev: any) => {
                    if (ev.detail.value) {
                      const time = new Date(ev.detail.value);
                      const hours = String(time.getHours()).padStart(2, '0');
                      const minutes = String(time.getMinutes()).padStart(2, '0');
                      rescheduleForm.end_time = `${hours}:${minutes}`;
                    }
                  }"
                ></ion-datetime>
              </ion-modal>
            </ion-item>

            <div class="ion-margin-top">
              <ion-button 
                expand="block" 
                color="primary" 
                @click="handleReschedule"
                :disabled="rescheduleLoading"
              >
                <ion-spinner v-if="rescheduleLoading" name="crescent"></ion-spinner>
                <span v-else>Reschedule</span>
              </ion-button>
            </div>
          </div>
        </ion-content>
      </ion-modal>
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
  IonButton,
  IonIcon,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonItem,
  IonLabel,
  IonModal,
  IonSelect,
  IonSelectOption,
  IonInput,
  IonDatetime,
  IonDatetimeButton,
  IonSpinner,
  IonNote,
  alertController,
  toastController,
} from '@ionic/vue';
import {
  locationOutline,
  peopleOutline,
  calendarOutline,
  timeOutline,
  personOutline,
  checkmarkCircleOutline,
  createOutline,
  gridOutline,
} from 'ionicons/icons';
import { reservationsApi } from '../api/reservations';
import { venuesApi } from '../api/venues';
import { useApi } from '../composables/useApi';
import { useAuthStore } from '../stores/auth';
import StatusBadge from '../components/StatusBadge.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { Reservation, Venue, RescheduleReservationData } from '../types';
import { formatTime } from '../utils/validators';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { loading, execute, showSuccess } = useApi<Reservation>();
const reservation = ref<Reservation | null>(null);

const canEdit = computed(() => {
  if (!reservation.value) return false;
  return (
    reservation.value.status === 'pending' &&
    reservation.value.user_id === authStore.user?.id
  );
});

const canReschedule = computed(() => {
  if (!reservation.value) return false;
  return (
    reservation.value.status === 'postponed' &&
    reservation.value.user_id === authStore.user?.id
  );
});

const canDelete = computed(() => {
  if (!reservation.value) return false;
  return (
    (reservation.value.status === 'pending' ||
      reservation.value.user_id === authStore.user?.id)
  );
});

async function loadReservation() {
  const id = parseInt(route.params.id as string);
  const data = await execute(() => reservationsApi.getById(id));
  if (data) {
    reservation.value = data;
  }
}

function goToEdit() {
  if (reservation.value) {
    router.push(`/reservations/${reservation.value.id}/edit`);
  }
}

async function handleDelete() {
  if (!reservation.value) return;

  const alert = await alertController.create({
    header: 'Delete Reservation',
    message: 'Are you sure you want to delete this reservation? This action cannot be undone.',
    buttons: [
      {
        text: 'Cancel',
        role: 'cancel',
      },
      {
        text: 'Delete',
        role: 'destructive',
        handler: async () => {
          try {
            await reservationsApi.delete(reservation.value!.id);
            showSuccess('Reservation deleted successfully');
            router.push('/reservations');
          } catch (error: any) {
            const toast = await toastController.create({
              message: error.response?.data?.message || 'Failed to delete reservation',
              duration: 3000,
              position: 'bottom',
              color: 'danger',
            });
            await toast.present();
          }
        },
      },
    ],
  });

  await alert.present();
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

function formatDateTime(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// Helper function to convert date string to ISO format for ion-datetime
function getDateValue(dateString: string | undefined): string | undefined {
  if (!dateString || dateString.trim() === '') {
    return undefined;
  }
  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
      return undefined;
    }
    return date.toISOString();
  } catch {
    return undefined;
  }
}

// Helper function to convert time string (HH:mm) to ISO datetime for ion-datetime
function getTimeValue(timeString: string | undefined, dateString: string | undefined): string | undefined {
  if (!timeString || timeString.trim() === '') {
    return undefined;
  }
  try {
    // Use provided date or today's date
    const date = dateString ? new Date(dateString) : new Date();
    const [hours, minutes] = timeString.split(':');
    
    if (!hours || !minutes) {
      return undefined;
    }
    
    date.setHours(parseInt(hours, 10));
    date.setMinutes(parseInt(minutes, 10));
    date.setSeconds(0);
    date.setMilliseconds(0);
    
    return date.toISOString();
  } catch {
    return undefined;
  }
}

// Reschedule modal state
const isRescheduleModalOpen = ref(false);
const rescheduleForm = ref<RescheduleReservationData>({});
const availableVenues = ref<Venue[]>([]);
const rescheduleLoading = ref(false);
const rescheduleErrors = ref<Record<string, string>>({});

const minRescheduleDate = computed(() => {
  // If venue is changed, use today as minimum date
  const selectedVenueId = rescheduleForm.value.venue_id;
  const isVenueChanged = selectedVenueId && selectedVenueId !== reservation.value?.venue_id;
  
  if (isVenueChanged) {
    // Different venue selected - any date from today is fine
    return new Date().toISOString().split('T')[0];
  }
  
  // Same venue (or no venue change) - check unavailable_until date
  if (!reservation.value?.venue.unavailable_until) {
    return new Date().toISOString().split('T')[0];
  }
  
  const unavailableUntil = new Date(reservation.value.venue.unavailable_until);
  const tomorrow = new Date(unavailableUntil);
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

async function openRescheduleModal() {
  if (!reservation.value) return;
  
  // Load all venues (including current venue even if unavailable)
  try {
    const venues = await venuesApi.getAll();
    const currentVenueId = reservation.value.venue_id;
    const currentVenue = reservation.value.venue;
    
    // Show only Santiago Campus venues, plus the current venue (even if it's not Santiago Campus or unavailable)
    // This allows users to reschedule to the same venue or change to another Santiago Campus venue
    availableVenues.value = venues.filter(v => 
      v.location === 'Santiago Campus' && (v.is_available || v.id === currentVenueId)
    );
    
    // If current venue is not in the list (e.g., it's Main Campus), add it
    if (currentVenue && !availableVenues.value.find(v => v.id === currentVenueId)) {
      availableVenues.value.push(currentVenue);
    }
    
    // Initialize form with current values
    rescheduleForm.value = {
      venue_id: reservation.value.venue_id,
      date: reservation.value.date,
      start_time: reservation.value.start_time,
      end_time: reservation.value.end_time,
    };
    
    isRescheduleModalOpen.value = true;
  } catch (error: any) {
    const toast = await toastController.create({
      message: 'Failed to load venues',
      duration: 3000,
      position: 'bottom',
      color: 'danger',
    });
    await toast.present();
  }
}

function closeRescheduleModal() {
  isRescheduleModalOpen.value = false;
  rescheduleForm.value = {};
  rescheduleErrors.value = {};
}

async function handleReschedule() {
  if (!reservation.value) return;
  
  rescheduleErrors.value = {};
  
  // Validate date based on selected venue
  if (rescheduleForm.value.date) {
    const selectedVenueId = rescheduleForm.value.venue_id || reservation.value.venue_id;
    const isVenueChanged = selectedVenueId !== reservation.value.venue_id;
    
    if (!isVenueChanged && reservation.value.venue.unavailable_until) {
      // Same venue - check that date is after unavailable_until
      const unavailableDate = new Date(reservation.value.venue.unavailable_until);
      const selectedDate = new Date(rescheduleForm.value.date);
      
      if (selectedDate <= unavailableDate) {
        rescheduleErrors.value.date = `Date must be after ${formatDate(reservation.value.venue.unavailable_until)}`;
        return;
      }
    }
    // If venue is changed, date validation is handled by backend (just needs to be after today)
  }
  
  // Build payload with only changed fields (exclude unchanged or empty values)
  const payload: RescheduleReservationData = {};
  
  // Check venue_id change
  if (rescheduleForm.value.venue_id !== undefined && 
      rescheduleForm.value.venue_id !== null && 
      rescheduleForm.value.venue_id !== reservation.value.venue_id) {
    payload.venue_id = Number(rescheduleForm.value.venue_id);
  }
  
  // Check date change
  if (rescheduleForm.value.date && 
      rescheduleForm.value.date.trim() !== '' && 
      rescheduleForm.value.date !== reservation.value.date) {
    payload.date = rescheduleForm.value.date;
  }
  
  // Check start_time change
  if (rescheduleForm.value.start_time && 
      rescheduleForm.value.start_time.trim() !== '' && 
      rescheduleForm.value.start_time !== reservation.value.start_time) {
    payload.start_time = rescheduleForm.value.start_time;
  }
  
  // Check end_time change
  if (rescheduleForm.value.end_time && 
      rescheduleForm.value.end_time.trim() !== '' && 
      rescheduleForm.value.end_time !== reservation.value.end_time) {
    payload.end_time = rescheduleForm.value.end_time;
  }
  
  // Validate that at least one field is changed
  if (Object.keys(payload).length === 0) {
    const toast = await toastController.create({
      message: 'Please change at least one field',
      duration: 3000,
      position: 'bottom',
      color: 'warning',
    });
    await toast.present();
    return;
  }
  
  rescheduleLoading.value = true;
  
  try {
    await reservationsApi.reschedule(reservation.value.id, payload);
    showSuccess('Reservation rescheduled successfully. Status changed to pending for admin approval.');
    closeRescheduleModal();
    await loadReservation();
    router.push('/reservations');
  } catch (error: any) {
    let errorMessage = 'Failed to reschedule reservation';
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      // Handle validation errors
      const errors = error.response.data.errors;
      const errorMessages = Object.values(errors).flat();
      errorMessage = errorMessages.join(', ');
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    const toast = await toastController.create({
      message: errorMessage,
      duration: 4000,
      position: 'bottom',
      color: 'danger',
    });
    await toast.present();
  } finally {
    rescheduleLoading.value = false;
  }
}

onMounted(() => {
  loadReservation();
});
</script>

<style scoped>
.reservation-detail-container {
  padding: 1rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-section {
  margin-bottom: 1.5rem;
}

.detail-section h3 {
  font-size: 1.1rem;
  margin-bottom: 0.75rem;
  color: var(--ion-color-primary);
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.info-item ion-icon {
  font-size: 1.2rem;
  color: var(--ion-color-primary);
}

.detail-section p {
  line-height: 1.6;
  color: var(--ion-color-dark);
}

.actions {
  padding: 1rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

.area-photo {
  margin-top: 1rem;
}

.area-photo img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  max-height: 300px;
  object-fit: cover;
}
</style>

