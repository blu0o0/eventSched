<template>
  <ion-card @click="$emit('click')">
    <ion-card-header>
      <ion-card-title>{{ venue.name }}</ion-card-title>
      <ion-card-subtitle>
        <ion-icon :icon="locationOutline" />
        {{ venue.location }}
      </ion-card-subtitle>
    </ion-card-header>
    <ion-card-content>
      <p v-if="venue.description">{{ venue.description }}</p>
      <div class="venue-info">
        <ion-badge color="primary">
          <ion-icon :icon="peopleOutline" />
          Max Occupancy: {{ venue.capacity }}
        </ion-badge>
      </div>
    </ion-card-content>
  </ion-card>
</template>

<script setup lang="ts">
import { IonCard, IonCardHeader, IonCardTitle, IonCardSubtitle, IonCardContent, IonBadge, IonIcon } from '@ionic/vue';
import { locationOutline, peopleOutline } from 'ionicons/icons';
import { Venue } from '../types';

interface Props {
  venue: Venue;
}

defineProps<Props>();
defineEmits<{
  click: [];
}>();
</script>

<style scoped>
ion-card {
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(45, 134, 89, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin: 0 0 16px 0;
  overflow: hidden;
}

ion-card:active {
  transform: scale(0.98);
  box-shadow: 0 2px 8px rgba(45, 134, 89, 0.15);
}

ion-card-header {
  background: linear-gradient(135deg, rgba(45, 134, 89, 0.05) 0%, rgba(30, 93, 63, 0.05) 100%);
  padding: 16px;
}

ion-card-title {
  font-weight: 600;
  color: var(--ion-color-primary);
  font-size: 1.1rem;
}

ion-card-subtitle {
  color: var(--ion-color-medium);
  font-size: 0.9rem;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}

ion-card-content {
  padding: 16px;
}

.venue-info {
  margin-top: 12px;
}

ion-badge {
  margin-right: 8px;
  border-radius: 8px;
  padding: 6px 10px;
  font-weight: 600;
}

ion-icon {
  margin-right: 4px;
  vertical-align: middle;
  color: var(--ion-color-primary);
}
</style>

