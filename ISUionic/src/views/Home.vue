<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Dashboard</ion-title>
        <ion-buttons slot="end">
          <div class="user-badge" v-if="authStore.isAuthenticated">
            <span class="user-name-text">{{ user.name }}</span>
            <ion-button @click="$router.push('/profile')">
              <ion-icon :icon="personCircleOutline" />
            </ion-button>
          </div>
          <ion-button v-else @click="$router.push('/login')">
            <ion-icon :icon="logInOutline" />
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="home-container">
        <!-- Main Row: Donut (Left) + Quick Actions (Right) -->
        <div class="dashboard-row">
          <!-- Left: Donut Chart -->
          <div class="dashboard-left">
            <ion-card class="chart-card">
              <ion-card-header>
                <ion-card-title>Reservation Overview</ion-card-title>
              </ion-card-header>
              <ion-card-content>
                <div class="chart-wrapper">
                  <div class="chart-container">
                    <canvas id="reservationChart"></canvas>
                  </div>
                  <div class="chart-legend">
                    <div class="legend-item">
                      <span class="legend-dot" style="background: linear-gradient(135deg, #ffd54f 0%, #ffca28 100%); border: 2px solid #f9a825;"></span>
                      <span>Pending</span>
                      <span class="legend-value">{{ stats.pending }}</span>
                    </div>
                    <div class="legend-item">
                      <span class="legend-dot" style="background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%); border: 2px solid #43a047;"></span>
                      <span>Approved</span>
                      <span class="legend-value">{{ stats.approved }}</span>
                    </div>
                    <div class="legend-item">
                      <span class="legend-dot" style="background: linear-gradient(135deg, #ef9a9a 0%, #e57373 100%); border: 2px solid #e53935;"></span>
                      <span>Rejected</span>
                      <span class="legend-value">{{ stats.rejected }}</span>
                    </div>
                    <hr>
                    <div class="legend-item total">
                      <span class="legend-dot" style="background: linear-gradient(135deg, #90caf9 0%, #64b5f6 100%); border: 2px solid #1e88e5;"></span>
                      <span><strong>Total Reservations</strong></span>
                      <span class="legend-value"><strong>{{ stats.total }}</strong></span>
                    </div>
                  </div>
                </div>
              </ion-card-content>
            </ion-card>
          </div>

          <!-- Right: Quick Actions (Laravel-style compact cards) -->
          <div class="dashboard-right">
            <div class="compact-action-card" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 1px solid #a5d6a7; border-radius: 16px; padding: 16px; cursor: pointer;" @click="handleCreateReservation">
              <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(67, 160, 71, 0.3);">
                  <ion-icon :icon="addCircleOutline" />
                </div>
                <div>
                  <p style="margin: 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #2e7d32;">Create Reservation</p>
                  <p style="margin: 2px 0 0; font-size: 12px; color: #558b2f; font-weight: 500;">Book a new venue</p>
                </div>
              </div>
            </div>

            <div class="compact-action-card" style="background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%); border: 1px solid #f48fb1; border-radius: 16px; padding: 16px; cursor: pointer;" @click="handleReportEmergency">
              <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #ef5350 0%, #c62828 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(239, 83, 80, 0.3);">
                  <ion-icon :icon="warningOutline" />
                </div>
                <div>
                  <p style="margin: 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #c62828;">Report Emergency</p>
                  <p style="margin: 2px 0 0; font-size: 12px; color: #b71c1c; font-weight: 500;">Report an urgent issue</p>
                </div>
              </div>
            </div>

            <div class="compact-action-card" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 1px solid #90caf9; border-radius: 16px; padding: 16px; cursor: pointer;" @click="handleViewMyRequests">
              <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #42a5f5 0%, #1565c0 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);">
                  <ion-icon :icon="listCircleOutline" />
                </div>
                <div>
                  <p style="margin: 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #1565c0;">View My Requests</p>
                  <p style="margin: 2px 0 0; font-size: 12px; color: #0d47a1; font-weight: 500;">Your bookings</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Emergency Section -->
        <div class="emergency-section" v-if="recentEmergencies.length > 0">
          <ion-card>
            <ion-card-header>
              <ion-card-title>
                <ion-icon :icon="warningOutline" style="margin-right: 8px; color: #ef5350;"></ion-icon>
                Recent Emergencies
              </ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <div class="emergency-list">
                <div v-for="emergency in recentEmergencies" :key="emergency.id" 
                     class="emergency-item" 
                     :class="{ 'emergency-open': emergency.status === 'open' }"
                     @click="goToEmergency(emergency.id)">
                  <div class="emergency-header">
                    <span class="emergency-type">{{ emergency.type }}</span>
                    <span class="emergency-status" :class="emergency.status === 'open' ? 'status-open' : 'status-resolved'">
                      {{ emergency.status === 'open' ? 'Open' : 'Resolved' }}
                    </span>
                  </div>
                  <p class="emergency-description">{{ emergency.description }}</p>
                  <div class="emergency-meta">
                    <small>By {{ emergency.reporter?.name || 'Unknown' }}</small>
                    <small>{{ formatDate(emergency.created_at) }}</small>
                  </div>
                </div>
              </div>
              <ion-button expand="block" fill="outline" color="danger" @click="handleViewAllEmergencies" style="margin-top: 12px;">
                View All Emergencies
              </ion-button>
            </ion-card-content>
          </ion-card>
        </div>

        <!-- Toggle: Calendar / Recent Reservations -->
        <div class="toggle-section">
          <div class="toggle-tabs">
            <button 
              class="toggle-tab" 
              :class="{ active: activeTab === 'calendar' }" 
              @click="activeTab = 'calendar'"
            >
              <ion-icon :icon="calendarOutline" />
              Calendar
            </button>
            <button 
              class="toggle-tab" 
              :class="{ active: activeTab === 'reservations' }" 
              @click="activeTab = 'reservations'"
            >
              <ion-icon :icon="listOutline" />
              Recent Reservations
            </button>
          </div>

          <!-- Calendar View -->
          <div v-if="activeTab === 'calendar'" class="calendar-section">
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

          <!-- Recent Reservations View (shows all) -->
          <div v-else class="recent-reservations">
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
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue';
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
  IonCardHeader,
  IonCardTitle,
  IonRefresher,
  IonRefresherContent,
  IonMenuButton,
} from '@ionic/vue';
import {
  personCircleOutline,
  calendarOutline,
  addCircleOutline,
  warningOutline,
  listCircleOutline,
  listOutline,
  logInOutline,
  alertCircleOutline,
} from 'ionicons/icons';
import ReservationCard from '../components/ReservationCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { reservationsApi } from '../api/reservations';
import { calendarApi } from '../api/calendar';
import { emergencyApi } from '../api/emergency';
import { useApi } from '../composables/useApi';
import { useAuthStore } from '../stores/auth';
import { useRequireAuth } from '../composables/useRequireAuth';
import { Reservation, CalendarEvent, EmergencyReport } from '../types';

const router = useRouter();
const authStore = useAuthStore();
const { requireAuth } = useRequireAuth();
const { loading, execute } = useApi<{ data: Reservation[]; meta?: any }>();
const { loading: calendarLoading, execute: executeCalendar } = useApi<CalendarEvent[]>();
const { loading: emergencyLoading, execute: executeEmergency } = useApi<{ data: EmergencyReport[]; meta?: any }>();
const recentReservations = ref<Reservation[]>([]);
const allReservations = ref<Reservation[]>([]);
const calendarEvents = ref<CalendarEvent[]>([]);
const recentEmergencies = ref<EmergencyReport[]>([]);
const openEmergenciesCount = ref(0);
const calendarRef = ref<InstanceType<typeof FullCalendar> | null>(null);
const activeTab = ref<'calendar' | 'reservations'>('calendar');

const user = computed(() => authStore.user || { name: 'User', id: 0 });

// Filter reservations to only show the current user's
const myReservations = computed(() => 
  allReservations.value.filter((r) => r.user_id === (user.value as any).id)
);

const stats = computed(() => {
  const total = allReservations.value.length;
  const pending = allReservations.value.filter((r) => r.status === 'pending').length;
  const approved = allReservations.value.filter((r) => r.status === 'approved').length;
  const rejected = allReservations.value.filter((r) => r.status === 'rejected').length;
  return { total, pending, approved, rejected };
});

const calendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: false as any,
  events: [],
  eventClick: (info: any) => {
    const event = info.event;
    const extendedProps = event.extendedProps;
    if (extendedProps && extendedProps.reservationId) {
      router.push(`/reservations/${extendedProps.reservationId}`);
    } else if (event.id) {
      router.push(`/reservations/${event.id}`);
    }
  },
  height: 'auto' as any,
  eventDisplay: 'block',
};

function initChart() {
  nextTick(() => {
    const canvas = document.getElementById('reservationChart') as HTMLCanvasElement;
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Destroy existing chart if any
    const existingChart = (window as any).__reservationChart;
    if (existingChart) {
      existingChart.destroy();
    }

    const total = stats.value.total;
    const chart = new (window as any).Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'Approved', 'Rejected'],
        datasets: [{
          data: [stats.value.pending, stats.value.approved, stats.value.rejected],
          backgroundColor: [
            'rgba(255, 213, 79, 0.85)',
            'rgba(129, 199, 132, 0.85)',
            'rgba(239, 154, 154, 0.85)'
          ],
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#111827',
            bodyColor: '#374151',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            boxPadding: 6,
            callbacks: {
              label: function(context: any) {
                const value = context.parsed;
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return context.label + ': ' + value + ' (' + percentage + '%)';
              }
            }
          }
        }
      },
      plugins: [{
        id: 'centerText',
        beforeDraw: function(chart: any) {
          const width = chart.width;
          const height = chart.height;
          const ctx = chart.ctx;
          ctx.restore();
          
          const text = total.toString();
          ctx.font = `bold ${height / 5}px Inter, sans-serif`;
          ctx.textBaseline = 'middle';
          ctx.fillStyle = '#111827';
          const textX = Math.round((width - ctx.measureText(text).width) / 2);
          const textY = height / 2 - 10;
          ctx.fillText(text, textX, textY);
          
          ctx.fillStyle = '#6b7280';
          ctx.font = `${height / 16}px Inter, sans-serif`;
          const subtext = 'Total';
          const subtextX = Math.round((width - ctx.measureText(subtext).width) / 2);
          ctx.fillText(subtext, subtextX, textY + height / 8);
          
          ctx.save();
        }
      }]
    });
    (window as any).__reservationChart = chart;
  });
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
    if (calendarRef.value) {
      const calendarApiInstance = calendarRef.value.getApi();
      calendarApiInstance.removeAllEvents();
      calendarApiInstance.addEventSource(data as any);
    }
  }
}

async function loadData() {
  const data = await execute(() => reservationsApi.getAll());
  if (data) {
    allReservations.value = data.data;
    recentReservations.value = data.data
      .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      .slice(0, 5);
    initChart();
  }
}

async function loadEmergencies() {
  try {
    const data = await executeEmergency(() => emergencyApi.getAll());
    if (data) {
      const emergencies = data.data || [];
      // Only show open emergencies
      const openEmergencies = emergencies.filter((e: EmergencyReport) => e.status === 'open');
      recentEmergencies.value = openEmergencies.slice(0, 3);
      openEmergenciesCount.value = openEmergencies.length;
    }
  } catch (error) {
    console.error('Failed to load emergencies:', error);
  }
}

async function handleRefresh(event: CustomEvent) {
  await Promise.all([loadData(), loadCalendarEvents(), loadEmergencies()]);
  (event.target as HTMLIonRefresherElement).complete();
}

function goToReservation(id: number) {
  router.push(`/reservations/${id}`);
}

async function handleCreateReservation() {
  const hasAccess = await requireAuth('You must be logged in to create a reservation.');
  if (hasAccess) {
    router.push('/reservations/create');
  }
}

async function handleReportEmergency() {
  router.push('/emergency/create');
}

async function handleViewMyRequests() {
  const hasAccess = await requireAuth('You must be logged in to view your requests.');
  if (hasAccess) {
    router.push('/reservations?mine=true');
  }
}

function handleViewEmergencies() {
  router.push('/emergency');
}

function handleViewAllEmergencies() {
  router.push('/emergency');
}

function goToEmergency(id: number) {
  router.push(`/emergency/${id}`);
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

onMounted(() => {
  loadData();
  loadCalendarEvents();
  loadEmergencies();
});
</script>

<style scoped>
.home-container {
  padding: 1rem;
}

/* Dashboard Row: Donut + Actions */
.dashboard-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.dashboard-left {
  flex: 1;
  min-width: 0;
}

.dashboard-right {
  width: 320px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Chart Card */
.chart-card {
  margin: 0;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
}

.chart-card ion-card-header {
  padding: 16px 20px 0;
}

.chart-card ion-card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #111827;
}

.chart-wrapper {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: 8px 0;
}

.chart-container {
  width: 200px;
  height: 200px;
  flex-shrink: 0;
}

.chart-container canvas {
  width: 100% !important;
  height: 100% !important;
}

.chart-legend {
  flex: 1;
  min-width: 0;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 0;
  font-size: 14px;
  color: #374151;
}

.legend-item.total {
  padding-top: 10px;
}

.legend-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  flex-shrink: 0;
}

.legend-value {
  margin-left: auto;
  font-weight: 700;
  font-size: 15px;
  color: #111827;
}

.chart-legend hr {
  margin: 6px 0;
  border: none;
  border-top: 1px solid #e5e7eb;
}

/* Compact Action Cards - Laravel Style with hover effect */
.compact-action-card {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.compact-action-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.compact-action-card:active {
  transform: scale(0.98);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.d-flex {
  display: flex;
}

.align-items-center {
  align-items: center;
}

.gap-3 {
  gap: 12px;
}

/* Toggle Section */
.toggle-section {
  margin-bottom: 2rem;
}

.toggle-tabs {
  display: flex;
  gap: 0;
  margin-bottom: 1rem;
  background: #f3f4f6;
  border-radius: 12px;
  padding: 4px;
  border: 1px solid #e5e7eb;
}

.toggle-tab {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  background: transparent;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.toggle-tab.active {
  background: white;
  color: #111827;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.toggle-tab ion-icon {
  font-size: 18px;
}

/* Calendar */
.calendar-container {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
}

.calendar {
  width: 100%;
  background: white;
}

/* Recent Reservations */
.recent-reservations {
  margin-bottom: 2rem;
}

.empty-state {
  text-align: center;
  color: #6b7280;
  padding: 2rem;
}

/* Emergency Section */
.emergency-section {
  margin-bottom: 2rem;
}

.emergency-section ion-card {
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
}

.emergency-section ion-card-header {
  padding: 16px 20px;
  background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);
}

.emergency-section ion-card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #b71c1c;
  display: flex;
  align-items: center;
}

.emergency-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.emergency-item {
  padding: 16px;
  border-radius: 12px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.2s ease;
}

.emergency-item:hover {
  background: #f3f4f6;
  transform: translateX(4px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.emergency-item.emergency-open {
  border-left: 4px solid #ef5350;
  background: #fef2f2;
}

.emergency-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.emergency-type {
  font-weight: 700;
  font-size: 15px;
  color: #111827;
}

.emergency-status {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-open {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.status-resolved {
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.emergency-description {
  color: #4b5563;
  font-size: 14px;
  line-height: 1.5;
  margin: 0 0 8px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.emergency-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  color: #6b7280;
}

/* User Badge in Header */
.user-badge {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-name-text {
  font-size: 13px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  white-space: nowrap;
}

@media (max-width: 768px) {
  .dashboard-row {
    flex-direction: column;
  }
  
  .dashboard-right {
    width: 100%;
  }
  
  .chart-wrapper {
    flex-direction: column;
  }
  
  .chart-container {
    width: 180px;
    height: 180px;
  }
}
</style>