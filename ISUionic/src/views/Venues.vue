<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-title>Venues</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="venues-container">
        <!-- Search Bar -->
        <ion-searchbar
          v-model="searchQuery"
          placeholder="Search venues..."
          @ionInput="filterVenues"
        ></ion-searchbar>

        <!-- Date Filter -->
        <ion-item>
          <ion-label>Filter by Date</ion-label>
          <ion-datetime-button datetime="datetime"></ion-datetime-button>
          <ion-modal :keep-contents-mounted="true">
            <ion-datetime
              id="datetime"
              presentation="date"
              :min="minDate"
              @ionChange="onDateChange"
            ></ion-datetime>
          </ion-modal>
        </ion-item>

        <!-- Loading State -->
        <LoadingSpinner v-if="loading && !venues.length" message="Loading venues..." />

        <!-- Venues List -->
        <div v-else-if="filteredVenues.length > 0">
          <VenueCard
            v-for="venue in filteredVenues"
            :key="venue.id"
            :venue="venue"
            @click="goToVenueDetail(venue.id)"
          />
        </div>

        <!-- Empty State -->
        <ion-card v-else>
          <ion-card-content>
            <p class="empty-state">No venues found</p>
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
  IonSearchbar,
  IonItem,
  IonLabel,
  IonCard,
  IonCardContent,
  IonRefresher,
  IonRefresherContent,
  IonDatetimeButton,
  IonModal,
  IonDatetime,
} from '@ionic/vue';
import { venuesApi } from '../api/venues';
import { useApi } from '../composables/useApi';
import VenueCard from '../components/VenueCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { Venue } from '../types';

const router = useRouter();
const { loading, execute } = useApi<Venue[]>();
const venues = ref<Venue[]>([]);
const searchQuery = ref('');
const selectedDate = ref<string | null>(null);

const minDate = new Date().toISOString().split('T')[0];

const filteredVenues = computed(() => {
  let filtered = venues.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (venue) =>
        venue.name.toLowerCase().includes(query) ||
        venue.location.toLowerCase().includes(query)
    );
  }

  return filtered;
});

function filterVenues() {
  // Filtering is handled by computed property
}

function onDateChange(event: CustomEvent) {
  const date = event.detail.value;
  selectedDate.value = date;
  // You can add date-based filtering logic here
}

async function loadVenues() {
  const data = await execute(() => venuesApi.getAll());
  if (data) {
    venues.value = data;
  }
}

async function handleRefresh(event: CustomEvent) {
  await loadVenues();
  (event.target as HTMLIonRefresherElement).complete();
}

function goToVenueDetail(id: number) {
  router.push(`/venues/${id}`);
}

onMounted(() => {
  loadVenues();
});
</script>

<style scoped>
.venues-container {
  padding: 1rem;
}

ion-searchbar {
  margin-bottom: 1rem;
}

.empty-state {
  text-align: center;
  color: var(--ion-color-medium);
  padding: 2rem;
}
</style>

