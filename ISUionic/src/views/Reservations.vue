<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>{{ showingMineOnly ? 'My Requests' : 'Reservations' }}</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="$router.push('/reservations/create')">
            <ion-icon :icon="addOutline" slot="icon-only" />
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <ion-segment v-model="selectedTab" @ionChange="onTabChange">
        <ion-segment-button value="all">
          <ion-label>All</ion-label>
        </ion-segment-button>
        <ion-segment-button value="pending">
          <ion-label>Pending</ion-label>
        </ion-segment-button>
        <ion-segment-button value="approved">
          <ion-label>Approved</ion-label>
        </ion-segment-button>
        <ion-segment-button value="postponed">
          <ion-label>Postponed</ion-label>
        </ion-segment-button>
        <ion-segment-button value="rejected">
          <ion-label>Rejected</ion-label>
        </ion-segment-button>
      </ion-segment>

      <div class="reservations-container">
        <LoadingSpinner v-if="loading && !reservations.length" message="Loading reservations..." />

        <div v-else-if="reservations.length > 0">
          <ReservationCard
            v-for="reservation in reservations"
            :key="reservation.id"
            :reservation="reservation"
            @click="goToReservationDetail(reservation.id)"
          />
        </div>

        <ion-card v-else>
          <ion-card-content>
            <p class="empty-state">
              {{
                selectedTab === 'all'
                  ? 'No reservations found'
                  : `No ${selectedTab} reservations found`
              }}
            </p>
          </ion-card-content>
        </ion-card>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
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
  IonSegment,
  IonSegmentButton,
  IonLabel,
  IonMenuButton,
  IonRefresher,
  IonRefresherContent,
} from '@ionic/vue';
import { addOutline } from 'ionicons/icons';
import ReservationCard from '../components/ReservationCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { reservationsApi } from '../api/reservations';
import { useApi } from '../composables/useApi';
import { Reservation } from '../types';

import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { loading, execute } = useApi<{ data: Reservation[]; meta?: any }>();
const reservations = ref<Reservation[]>([]);
const selectedTab = ref<'all' | 'pending' | 'approved' | 'postponed' | 'rejected'>('all');
const showingMineOnly = ref(false);

async function loadReservations() {
  const status = selectedTab.value === 'all' ? undefined : selectedTab.value;
  const mine = route.query.mine === 'true';
  const data = await execute(() => reservationsApi.getAll(status, mine));
  if (data) {
    reservations.value = data.data;
    showingMineOnly.value = mine;
  }
}

function onTabChange() {
  loadReservations();
}

async function handleRefresh(event: CustomEvent) {
  await loadReservations();
  (event.target as HTMLIonRefresherElement).complete();
}

function goToReservationDetail(id: number) {
  router.push(`/reservations/${id}`);
}

onMounted(() => {
  loadReservations();
});
</script>

<style scoped>
.reservations-container {
  padding: 1rem;
}

.empty-state {
  text-align: center;
  color: var(--ion-color-medium);
  padding: 2rem;
}
</style>

