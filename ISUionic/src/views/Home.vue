<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Dashboard</ion-title>
        <ion-buttons slot="end">
          <div class="user-badge">
            <span class="user-name-text">{{ user.name || 'User' }}</span>
            <ion-button @click="$router.push('/profile')">
              <ion-icon :icon="personCircleOutline" />
            </ion-button>
          </div>
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

          <!-- Right: Quick Actions -->
          <div class="dashboard-right">
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
                  <ion-icon :icon="chevronForwardOutline" class="action-arrow" />
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
                  <ion-icon :icon="chevronForwardOutline" class="action-arrow" />
                </div>
              </ion-card-content>
            </ion-card>
          </div>
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

          <!-- Recent Reservations View -->
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
  chevronForwardOutline,
  listOutline,
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
const activeTab = ref<'calendar' | 'reservations'>('calendar');

const user = computed(() => authStore.user || { name: 'User' });

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
  headerToolbar: false,
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
  height: 'auto',
  eventDisplay: 'block',
};

function changeView(view: string) {
  if (calendarRef.value) {
    const calendarApiInstance = calendarRef.value.getApi();
    calendarApiInstance.changeView(view);
  }
}

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
    const chart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'Approved', 'Rejected'],
        datasets: [{
          data: [stats.value.pending, stats.value.approved, stats.value.rejected],
          backgroundColor: [
            'rgba(255, 202, 40, 0.9)',
            'rgba(102, 187, 106, 0.9)',
            'rgba(229, 115, 115, 0.9)'
          ],
          borderColor: ['#f9a825', '#43a047', '#e53935'],
          borderWidth: 3,
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
      calendarApiInstance.addEventSource(data);
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
  gap: 1rem;
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

/* Action Cards */
.action-card {
  margin: 0;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.action-card:active {
  transform: scale(0.98);
}

.action-card-primary {
  border-left: 4px solid var(--ion-color-primary);
}

.action-card-danger {
  border-left: 4px solid var(--ion-color-danger);
}

.action-content {
  display: flex;
  align-items: center;
  gap: 14px;
}

.action-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 12px;
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

.action-icon-danger {
  background: linear-gradient(135deg, var(--ion-color-danger) 0%, rgba(220, 53, 69, 1) 100%);
  color: white;
}

.action-icon-wrapper ion-icon {
  font-size: 1.5rem;
}

.action-text {
  flex: 1;
  min-width: 0;
}

.action-text h3 {
  margin: 0 0 2px 0;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  line-height: 1.3;
}

.action-text p {
  margin: 0;
  font-size: 0.8rem;
  color: #6b7280;
  line-height: 1.4;
}

.action-arrow {
  font-size: 1.2rem;
  color: #9ca3af;
  flex-shrink: 0;
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