<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Dashboard</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="$router.push('/profile')">
            <ion-icon :icon="personCircleOutline" />
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="home-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
          <h1>Welcome, {{ user.name || 'User' }}!</h1>
          <p>Manage your venue reservations and reports</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" v-if="stats">
          <ion-card class="stat-card">
            <ion-card-content>
              <div class="stat-content">
                <ion-icon :icon="calendarOutline" color="primary" />
                <div>
                  <h2>{{ stats.total }}</h2>
                  <p>Total Reservations</p>
                </div>
              </div>
            </ion-card-content>
          </ion-card>

          <ion-card class="stat-card">
            <ion-card-content>
              <div class="stat-content">
                <ion-icon :icon="timeOutline" color="warning" />
                <div>
                  <h2>{{ stats.pending }}</h2>
                  <p>Pending</p>
                </div>
              </div>
            </ion-card-content>
          </ion-card>

          <ion-card class="stat-card">
            <ion-card-content>
              <div class="stat-content">
                <ion-icon :icon="checkmarkCircleOutline" color="success" />
                <div>
                  <h2>{{ stats.approved }}</h2>
                  <p>Approved</p>
                </div>
              </div>
            </ion-card-content>
          </ion-card>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <h2>Quick Actions</h2>
          <div class="actions-grid">
            <ion-card class="action-card action-card-primary" @click="$router.push('/reservations/create')">
              <ion-card-content>
                <div class="action-content">
                  <div class="action-icon-wrapper action-icon-primary">
                    <ion-icon :icon="addCircleOutline" />
                  </div>
                  <div class="action-text">
                    <h3>Create Reservation</h3>
                    <p>Book a new venue reservation</p>
                  </div>
                </div>
              </ion-card-content>
            </ion-card>
            
            <ion-card class="action-card action-card-danger" @click="$router.push('/emergency/create')">
              <ion-card-content>
                <div class="action-content">
                  <div class="action-icon-wrapper action-icon-danger">
                    <ion-icon :icon="warningOutline" />
                  </div>
                  <div class="action-text">
                    <h3>Report Emergency</h3>
                    <p>Report an urgent issue</p>
                  </div>
                </div>
              </ion-card-content>
            </ion-card>
          </div>
        </div>

        <!-- Calendar Section -->
        <div class="calendar-section">
          <div class="section-header">
            <h2>Calendar</h2>
            <ion-button fill="clear" size="small" @click="changeView('dayGridMonth')">
              <ion-icon :icon="calendarOutline" slot="start" />
              Month
            </ion-button>
            <ion-button fill="clear" size="small" @click="changeView('timeGridWeek')">
              <ion-icon :icon="gridOutline" slot="start" />
              Week
            </ion-button>
            <ion-button fill="clear" size="small" @click="changeView('timeGridDay')">
              <ion-icon :icon="todayOutline" slot="start" />
              Day
            </ion-button>
          </div>

          <div class="calendar-container">
            <LoadingSpinner v-if="calendarLoading && !calendarEvents.length" message="Loading calendar events..." />
            <FullCalendar
              v-else
              ref="calendarRef"
              :options="calendarOptions"
              class="calendar"
            />
          </div>
        </div>

        <!-- Recent Reservations -->
        <div class="recent-reservations">
          <div class="section-header">
            <h2>Recent Reservations</h2>
            <ion-button fill="clear" size="small" @click="$router.push('/reservations')">
              View All
            </ion-button>
          </div>

          <LoadingSpinner v-if="loading && !recentReservations.length" />
          <div v-else-if="recentReservations.length > 0">
            <ReservationCard
              v-for="reservation in recentReservations"
              :key="reservation.id"
              :reservation="reservation"
              @click="goToReservation(reservation.id)"
            />
          </div>
          <ion-card v-else>
            <ion-card-content>
              <p class="empty-state">No reservations yet. Create your first reservation!</p>
            </ion-card-content>
          </ion-card>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButton,
  IonButtons,
  IonIcon,
  IonCard,
  IonCardContent,
  IonRefresher,
  IonRefresherContent,
  IonMenuButton,
} from '@ionic/vue';
import {
  personCircleOutline,
  calendarOutline,
  timeOutline,
  checkmarkCircleOutline,
  addCircleOutline,
  warningOutline,
  gridOutline,
  todayOutline,
} from 'ionicons/icons';
import ReservationCard from '../components/ReservationCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { reservationsApi } from '../api/reservations';
import { calendarApi } from '../api/calendar';
import { useApi } from '../composables/useApi';
import { useAuthStore } from '../stores/auth';
import { Reservation, CalendarEvent } from '../types';

const router = useRouter();
const authStore = useAuthStore();
const { loading, execute } = useApi<{ data: Reservation[]; meta?: any }>();
const { loading: calendarLoading, execute: executeCalendar } = useApi<CalendarEvent[]>();
const recentReservations = ref<Reservation[]>([]);
const allReservations = ref<Reservation[]>([]);
const calendarEvents = ref<CalendarEvent[]>([]);
const calendarRef = ref<InstanceType<typeof FullCalendar> | null>(null);

const user = computed(() => authStore.user);

const stats = computed(() => {
  const total = allReservations.value.length;
  const pending = allReservations.value.filter((r) => r.status === 'pending').length;
  const approved = allReservations.value.filter((r) => r.status === 'approved').length;
  return { total, pending, approved };
});

const calendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: false,
  events: [],
  eventClick: (info: any) => {
    // Extract reservation ID from event if available
    const event = info.event;
    const extendedProps = event.extendedProps;
    if (extendedProps && extendedProps.reservationId) {
      router.push(`/reservations/${extendedProps.reservationId}`);
    } else if (event.id) {
      // Fallback: use event ID as reservation ID
      router.push(`/reservations/${event.id}`);
    }
  },
  height: 'auto',
  eventDisplay: 'block',
};

function changeView(view: string) {
  if (calendarRef.value) {
    const calendarApiInstance = calendarRef.value.getApi();
    calendarApiInstance.changeView(view);
  }
}

async function loadCalendarEvents() {
  const today = new Date();
  const start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
  const end = new Date(today.getFullYear(), today.getMonth() + 2, 0);
  
  const data = await executeCalendar(() =>
    calendarApi.getEvents(start.toISOString().split('T')[0], end.toISOString().split('T')[0])
  );
  
  if (data) {
    calendarEvents.value = data;
    
    // If calendar is already initialized, update it
    if (calendarRef.value) {
      const calendarApiInstance = calendarRef.value.getApi();
      calendarApiInstance.removeAllEvents();
      calendarApiInstance.addEventSource(data);
    }
  }
}

async function loadData() {
  // Load all reservations to calculate stats and get recent ones
  const data = await execute(() => reservationsApi.getAll());
  if (data) {
    allReservations.value = data.data;
    // Get the 5 most recent reservations
    recentReservations.value = data.data
      .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      .slice(0, 5);
  }
}

async function handleRefresh(event: CustomEvent) {
  await Promise.all([loadData(), loadCalendarEvents()]);
  (event.target as HTMLIonRefresherElement).complete();
}

function goToReservation(id: number) {
  router.push(`/reservations/${id}`);
}

onMounted(() => {
  loadData();
  loadCalendarEvents();
});
</script>

<style scoped>
.home-container {
  padding: 1rem;
}

.welcome-section {
  margin-bottom: 2rem;
  text-align: left;
  padding: 1rem 0;
}

.welcome-section h1 {
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
  color: var(--ion-color-dark);
  font-weight: 700;
}

.welcome-section p {
  color: var(--ion-color-medium);
  font-size: 0.95rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  margin: 0;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card:active {
  transform: scale(0.98);
  box-shadow: 0 2px 8px rgba(45, 134, 89, 0.15);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-content ion-icon {
  font-size: 2rem;
}

.stat-content h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
}

.stat-content p {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ion-color-medium);
}

.quick-actions {
  margin-bottom: 2rem;
}

.quick-actions h2 {
  margin-bottom: 1.25rem;
  font-size: 1.3rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.actions-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.action-card {
  margin: 0;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  overflow: hidden;
  border: none;
}

.action-card:active {
  transform: scale(0.98);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.action-card-primary {
  background: linear-gradient(135deg, rgba(45, 134, 89, 0.1) 0%, rgba(30, 93, 63, 0.05) 100%);
  border-left: 4px solid var(--ion-color-primary);
}

.action-card-secondary {
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.1) 0%, rgba(25, 118, 210, 0.05) 100%);
  border-left: 4px solid #2196f3;
}

.action-card-danger {
  background: linear-gradient(135deg, rgba(235, 68, 90, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
  border-left: 4px solid var(--ion-color-danger);
}

.action-card-content {
  padding: 1.25rem !important;
}

.action-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.action-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-icon-primary {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, rgba(30, 93, 63, 1) 100%);
  color: white;
}

.action-icon-secondary {
  background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
  color: white;
}

.action-icon-danger {
  background: linear-gradient(135deg, var(--ion-color-danger) 0%, rgba(220, 53, 69, 1) 100%);
  color: white;
}

.action-icon-wrapper ion-icon {
  font-size: 1.75rem;
}

.action-text {
  flex: 1;
}

.action-text h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
  line-height: 1.3;
}

.action-text p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
  line-height: 1.4;
}

.calendar-section {
  margin-bottom: 2rem;
}

.calendar-container {
  margin-top: 1rem;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.calendar {
  width: 100%;
  background: white;
}

.recent-reservations {
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.section-header h2 {
  margin: 0;
  font-size: 1.3rem;
}

.empty-state {
  text-align: center;
  color: var(--ion-color-medium);
  padding: 2rem;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>

