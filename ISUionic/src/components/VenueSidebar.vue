<template>
  <ion-list lines="full" class="venue-sidebar">
    <ion-list-header>
      <ion-label>
        <h2>Venues</h2>
        <p class="venue-count">{{ venues.length }} venue(s) found</p>
      </ion-label>
    </ion-list-header>

    <ion-item
      v-for="venue in venues"
      :key="venue.id"
      :button="true"
      :detail="false"
      :class="['venue-item', { 'venue-selected': selectedVenueId === venue.id }]"
      @click="selectVenue(venue)"
    >
      <ion-avatar slot="start" class="venue-avatar">
        <img
          v-if="venue.photo_url"
          :src="venue.photo_url"
          :alt="venue.name"
          class="venue-photo"
        />
        <div
          v-else
          class="status-indicator"
          :class="getAvailabilityClass(venue.id)"
        ></div>
      </ion-avatar>

      <ion-label>
        <h3 class="venue-name">{{ venue.name }}</h3>
        <div class="venue-details">
          <span class="venue-reservations">
            <ion-icon :icon="peopleOutline" class="detail-icon"></ion-icon>
            {{ getReservationCount(venue.id) }} reservation(s)
          </span>
          <span
            class="venue-status"
            :class="getAvailabilityClass(venue.id)"
          >
            {{ getAvailabilityText(venue.id) }}
          </span>
        </div>
      </ion-label>

      <ion-icon
        :icon="chevronForwardOutline"
        slot="end"
        class="chevron-icon"
      ></ion-icon>
    </ion-item>

    <ion-item v-if="venues.length === 0" class="no-venues">
      <ion-label class="ion-text-center">
        <p>No venues available</p>
      </ion-label>
    </ion-item>
  </ion-list>
</template>

<script setup lang="ts">
import {
  IonList,
  IonListHeader,
  IonItem,
  IonLabel,
  IonAvatar,
  IonIcon,
} from '@ionic/vue';
import { peopleOutline, chevronForwardOutline } from 'ionicons/icons';
import { Venue } from '../types';

interface Props {
  venues: Venue[];
  venueAvailability: Record<number, any>;
  selectedVenueId?: number | null;
}

interface Emits {
  (e: 'venue-select', venue: Venue): void;
}

const props = withDefaults(defineProps<Props>(), {
  selectedVenueId: null,
});

const emit = defineEmits<Emits>();

function getAvailabilityClass(venueId: number): string {
  const availability = props.venueAvailability[venueId];
  if (!availability) return 'no-coords';

  if (availability.is_available) {
    return 'available';
  } else if (availability.is_currently_occupied) {
    return 'occupied';
  } else {
    return 'reserved';
  }
}

function getAvailabilityText(venueId: number): string {
  const availability = props.venueAvailability[venueId];
  if (!availability) return 'No Data';

  if (availability.is_available) {
    return 'Available';
  } else if (availability.is_currently_occupied) {
    return 'In Use';
  } else {
    return 'Reserved';
  }
}

function getReservationCount(venueId: number): number {
  const availability = props.venueAvailability[venueId];
  return availability?.total_reservations ?? 0;
}

function selectVenue(venue: Venue): void {
  emit('venue-select', venue);
}
</script>

<style scoped>
.venue-sidebar {
  background: var(--ion-color-light);
  height: 100%;
  overflow-y: auto;
}

ion-list-header {
  background: var(--ion-color-primary);
  color: white;
  padding: 16px;
}

ion-list-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.venue-count {
  margin: 4px 0 0 0;
  font-size: 12px;
  opacity: 0.9;
  color: #86efac;
}

.venue-item {
  --background: white;
  --padding-start: 12px;
  --padding-end: 12px;
  --inner-padding-end: 8px;
  margin-bottom: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.venue-item:hover {
  --background: #f8f9fa;
  transform: translateX(4px);
}

.venue-selected {
  --background: #e3f2fd;
  border-left: 4px solid var(--ion-color-primary);
}

.venue-avatar {
  width: 48px;
  height: 48px;
  margin-right: 12px;
}

.venue-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.status-indicator {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.status-indicator.available {
  background-color: #28a745;
}

.status-indicator.occupied {
  background-color: #dc3545;
}

.status-indicator.reserved {
  background-color: #ffc107;
}

.status-indicator.no-coords {
  background-color: #6c757d;
}

.venue-name {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.venue-details {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.venue-reservations {
  font-size: 12px;
  color: #4b5563;
  display: flex;
  align-items: center;
  gap: 4px;
}

.detail-icon {
  font-size: 14px;
  color: var(--ion-color-medium);
}

.venue-status {
  font-size: 11px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.venue-status.available {
  background-color: #d4edda;
  color: #155724;
}

.venue-status.occupied {
  background-color: #f8d7da;
  color: #721c24;
}

.venue-status.reserved {
  background-color: #fff3cd;
  color: #856404;
}

.chevron-icon {
  color: var(--ion-color-medium);
  font-size: 20px;
}

.no-venues {
  --background: transparent;
  text-align: center;
  padding: 32px 16px;
}

.no-venues p {
  color: var(--ion-color-medium);
  font-size: 14px;
  margin: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .venue-item {
    --padding-start: 8px;
    --padding-end: 8px;
  }

  .venue-avatar {
    width: 40px;
    height: 40px;
  }

  .venue-name {
    font-size: 14px;
  }

  .venue-details {
    gap: 8px;
  }

  .venue-reservations,
  .venue-status {
    font-size: 11px;
  }
}
</style>