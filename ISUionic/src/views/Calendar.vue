<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Calendar</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="changeView('dayGridMonth')">
            <ion-icon :icon="calendarOutline" />
          </ion-button>
          <ion-button @click="changeView('timeGridWeek')">
            <ion-icon :icon="gridOutline" />
          </ion-button>
          <ion-button @click="changeView('timeGridDay')">
            <ion-icon :icon="todayOutline" />
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="calendar-container">
        <LoadingSpinner v-if="loading && !events.length" message="Loading calendar events..." />

        <FullCalendar
          v-else
          ref="calendarRef"
          :options="calendarOptions"
          class="calendar"
        />
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
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
  IonRefresher,
  IonRefresherContent,
  IonMenuButton,
} from '@ionic/vue';
import { calendarOutline, gridOutline, todayOutline } from 'ionicons/icons';
import { calendarApi } from '../api/calendar';
import { useApi } from '../composables/useApi';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { CalendarEvent } from '../types';
import { useRouter } from 'vue-router';

const router = useRouter();
const { loading, execute } = useApi<CalendarEvent[]>();
const events = ref<CalendarEvent[]>([]);
const currentView = ref('dayGridMonth');
const calendarRef = ref<InstanceType<typeof FullCalendar> | null>(null);

const calendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: '',
  },
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
  currentView.value = view;
  if (calendarRef.value) {
    const calendarApi = calendarRef.value.getApi();
    calendarApi.changeView(view);
  }
}

async function loadEvents() {
  const today = new Date();
  const start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
  const end = new Date(today.getFullYear(), today.getMonth() + 2, 0);
  
  const data = await execute(() =>
    calendarApi.getEvents(start.toISOString().split('T')[0], end.toISOString().split('T')[0])
  );
  
  if (data) {
    events.value = data;
    
    // If calendar is already initialized, update it
    if (calendarRef.value) {
      const calendarApiInstance = calendarRef.value.getApi();
      calendarApiInstance.removeAllEvents();
      calendarApiInstance.addEventSource(data);
    }
  }
}

async function handleRefresh(event: CustomEvent) {
  await loadEvents();
  (event.target as HTMLIonRefresherElement).complete();
}

onMounted(() => {
  loadEvents();
});
</script>

<style scoped>
.calendar-container {
  padding: 1rem;
}

.calendar {
  width: 100%;
}
</style>

