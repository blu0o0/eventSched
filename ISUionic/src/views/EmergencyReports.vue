<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Emergency</ion-title>
    <ion-buttons slot="end">
      <ion-button v-if="isAuthenticated" @click="$router.push('/emergency/create')">
        <ion-icon :icon="addOutline" slot="icon-only" />
      </ion-button>
    </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <ion-fab v-if="isAuthenticated" vertical="bottom" horizontal="end" slot="fixed">
        <ion-fab-button @click="$router.push('/emergency/create')">
          <ion-icon :icon="addOutline"></ion-icon>
        </ion-fab-button>
      </ion-fab>

      <ion-refresher slot="fixed" @ionRefresh="handleRefresh($event)">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="emergency-container">
        <LoadingSpinner v-if="loading && !reports.length" message="Loading emergency reports..." />

        <div v-else-if="reports.length > 0">
          <ion-card
            v-for="report in reports"
            :key="report.id"
            @click="goToReportDetail(report.id)"
            class="report-card"
          >
            <ion-card-header>
              <div class="card-header">
                <ion-card-title>{{ report.type }}</ion-card-title>
                <StatusBadge :status="report.status" />
              </div>
              <ion-card-subtitle>
                {{ formatDateTime(report.created_at) }}
              </ion-card-subtitle>
            </ion-card-header>
            <ion-card-content>
              <p class="description">{{ truncateText(report.description, 150) }}</p>
              <div class="reporter-info">
                <ion-icon :icon="personOutline" />
                <span>Reported by: {{ report.reporter.name }}</span>
              </div>
              <div v-if="report.status === 'closed' && report.resolved_at" class="resolved-info">
                <ion-icon :icon="checkmarkCircleOutline" />
                <span>Resolved on {{ formatDateTime(report.resolved_at) }}</span>
              </div>
            </ion-card-content>
          </ion-card>
        </div>

        <ion-card v-else>
          <ion-card-content>
            <p class="empty-state">No emergency reports found</p>
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
  IonCardHeader,
  IonCardTitle,
  IonCardSubtitle,
  IonCardContent,
  IonFab,
  IonFabButton,
  IonRefresher,
  IonRefresherContent,
  IonMenuButton,
} from '@ionic/vue';
import { addOutline, personOutline, checkmarkCircleOutline } from 'ionicons/icons';
import { emergencyApi } from '../api/emergency';
import { useApi } from '../composables/useApi';
import { useAuthStore } from '../stores/auth';
import StatusBadge from '../components/StatusBadge.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import { EmergencyReport } from '../types';

const router = useRouter();
const authStore = useAuthStore();
const { loading, execute } = useApi<{ data: EmergencyReport[]; meta?: any }>();
const reports = ref<EmergencyReport[]>([]);
const isAuthenticated = computed(() => authStore.isAuthenticated);

async function loadReports() {
  const data = await execute(() => emergencyApi.getAll());
  if (data) {
    reports.value = data.data;
  }
}

async function handleRefresh(event: CustomEvent) {
  await loadReports();
  (event.target as HTMLIonRefresherElement).complete();
}

function goToReportDetail(id: number) {
  router.push(`/emergency/${id}`);
}

function formatDateTime(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function truncateText(text: string, maxLength: number): string {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}

onMounted(() => {
  loadReports();
});
</script>

<style scoped>
.emergency-container {
  padding: 1rem;
}

.report-card {
  margin-bottom: 1rem;
  cursor: pointer;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.description {
  margin: 0.75rem 0;
  line-height: 1.6;
  color: var(--ion-color-dark);
}

.reporter-info,
.resolved-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.75rem;
  font-size: 0.9rem;
  color: var(--ion-color-medium);
}

.resolved-info {
  color: var(--ion-color-success);
}

.empty-state {
  text-align: center;
  color: var(--ion-color-medium);
  padding: 2rem;
}
</style>

