<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button></ion-back-button>
        </ion-buttons>
        <ion-title>Venue Details</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <LoadingSpinner v-if="loading && !venue" message="Loading venue details..." />

      <div v-else-if="venue" class="venue-detail-container">
        <ion-card>
          <ion-card-header>
            <ion-card-title>{{ venue.name }}</ion-card-title>
            <ion-card-subtitle>
              <ion-icon :icon="locationOutline" />
              {{ venue.location }}
            </ion-card-subtitle>
          </ion-card-header>
          <ion-card-content>
            <div class="venue-info">
              <div class="info-item">
                <ion-icon :icon="peopleOutline" />
                <span>Max Occupancy: {{ venue.capacity }} people</span>
              </div>
            </div>

            <div v-if="venue.description" class="description">
              <h3>Description</h3>
              <p>{{ venue.description }}</p>
            </div>

            <!-- Map View (if coordinates available) -->
            <div v-if="venue.map_coordinates" class="map-section">
              <h3>Location</h3>
              <div class="map-placeholder">
                <ion-icon :icon="mapOutline" />
                <p>Map coordinates: {{ venue.map_coordinates }}</p>
                <ion-button
                  fill="outline"
                  size="small"
                  @click="openMap"
                >
                  Open in Maps
                </ion-button>
              </div>
            </div>
          </ion-card-content>
        </ion-card>

        <div class="actions">
          <ion-button expand="block" @click="goToCreateReservation">
            Reserve This Venue
          </ion-button>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonBackButton,
  IonButtons,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardSubtitle,
  IonCardContent,
  IonButton,
  IonIcon,
} from '@ionic/vue';
import { locationOutline, peopleOutline, mapOutline } from 'ionicons/icons';
import { venuesApi } from '../api/venues';
import { useApi } from '../composables/useApi';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { Venue } from '../types';

const route = useRoute();
const router = useRouter();
const { loading, execute } = useApi<Venue>();
const venue = ref<Venue | null>(null);

async function loadVenue() {
  const id = parseInt(route.params.id as string);
  const data = await execute(() => venuesApi.getById(id));
  if (data) {
    venue.value = data;
  }
}

function goToCreateReservation() {
  if (venue.value) {
    router.push({
      path: '/reservations/create',
      query: { venue_id: venue.value.id.toString() },
    });
  }
}

function openMap() {
  if (venue.value?.map_coordinates) {
    const [lat, lng] = venue.value.map_coordinates.split(',');
    // Open in device maps app
    window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
  }
}

onMounted(() => {
  loadVenue();
});
</script>

<style scoped>
.venue-detail-container {
  padding: 1rem;
}

.venue-info {
  margin-bottom: 1.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.description {
  margin-top: 1.5rem;
}

.description h3 {
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  color: var(--ion-color-primary);
}

.description p {
  line-height: 1.6;
  color: var(--ion-color-dark);
}

.map-section {
  margin-top: 1.5rem;
}

.map-section h3 {
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  color: var(--ion-color-primary);
}

.map-placeholder {
  border: 2px dashed var(--ion-color-medium);
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  background: var(--ion-color-light);
}

.map-placeholder ion-icon {
  font-size: 3rem;
  color: var(--ion-color-medium);
  margin-bottom: 1rem;
}

.actions {
  padding: 1rem;
}
</style>

