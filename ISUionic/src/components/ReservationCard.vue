<template>
  <ion-card @click="$emit('click')">
    <ion-card-header>
      <div class="card-header">
        <ion-card-title>{{ reservation.title }}</ion-card-title>
        <div class="badge-group">
          <span v-if="reservation.is_edited" class="edited-badge">
            <ion-icon :icon="pencilOutline" /> Updated
          </span>
          <StatusBadge :status="reservation.status" />
        </div>
      </div>
      <ion-card-subtitle>
        <ion-icon :icon="locationOutline" />
        {{ reservation.venue.name }}
      </ion-card-subtitle>
    </ion-card-header>
    <ion-card-content>
      <div class="reservation-info">
        <div class="info-item">
          <ion-icon :icon="calendarOutline" />
          <span>{{ formatDate(reservation.date) }}</span>
        </div>
        <div class="info-item">
          <ion-icon :icon="timeOutline" />
          <span>{{ reservation.start_time }} - {{ reservation.end_time }}</span>
        </div>
        <div class="info-item">
          <ion-icon :icon="peopleOutline" />
          <span>Max Occupancy: {{ reservation.capacity }}</span>
        </div>
      </div>
      <p v-if="reservation.description" class="description">
        {{ truncateText(reservation.description, 100) }}
      </p>
    </ion-card-content>
  </ion-card>
</template>

<script setup lang="ts">
import { IonCard, IonCardHeader, IonCardTitle, IonCardSubtitle, IonCardContent, IonIcon } from '@ionic/vue';
import { calendarOutline, timeOutline, peopleOutline, locationOutline, pencilOutline } from 'ionicons/icons';
import { Reservation } from '../types';
import StatusBadge from './StatusBadge.vue';

interface Props {
  reservation: Reservation;
}

defineProps<Props>();
defineEmits<{
  click: [];
}>();

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    weekday: 'short', 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
}

function truncateText(text: string, maxLength: number): string {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}
</script>

<style scoped>
.badge-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.edited-badge {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #fff7ed;
  color: #ea580c;
  border: 1px solid #fed7aa;
  padding: 3px 8px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.edited-badge ion-icon {
  font-size: 12px;
  color: #ea580c;
}

ion-card {
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(45, 134, 89, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin: 0 0 16px 0;
  overflow: hidden;
  border-left: 4px solid var(--ion-color-primary);
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

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.reservation-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: var(--ion-color-medium);
}

ion-icon {
  font-size: 1.1rem;
  color: var(--ion-color-primary);
}

.description {
  margin-top: 0.75rem;
  color: var(--ion-color-dark);
  font-size: 0.9rem;
  line-height: 1.4;
  padding: 12px;
  background: rgba(45, 134, 89, 0.03);
  border-radius: 8px;
}
</style>

