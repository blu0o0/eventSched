<template>
  <ion-menu side="start" menu-id="main-menu" content-id="main-content">
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>
          <div class="menu-header">
            <ion-icon :icon="calendarOutline" class="menu-logo" />
            <div class="menu-title">
              <h2>Event Scheduling</h2>
              <p>ISU Santiago</p>
            </div>
          </div>
        </ion-title>
      </ion-toolbar>
    </ion-header>
    <ion-content>
      <ion-list>
        <ion-item 
          button 
          :class="{ 'menu-item-active': $route.name === 'Home' }"
          @click="navigateTo('/home')"
        >
          <ion-icon :icon="homeOutline" slot="start" />
          <ion-label>Dashboard</ion-label>
        </ion-item>

        <ion-item 
          button 
          :class="{ 'menu-item-active': $route.name === 'Reservations' || $route.name === 'CreateReservation' || $route.name === 'ReservationDetail' || $route.name === 'EditReservation' }"
          @click="navigateTo('/reservations')"
        >
          <ion-icon :icon="calendarOutline" slot="start" />
          <ion-label>Reservations</ion-label>
        </ion-item>

        <ion-item 
          button 
          :class="{ 'menu-item-active': $route.name === 'EmergencyReports' || $route.name === 'CreateEmergency' || $route.name === 'EmergencyDetail' }"
          @click="navigateTo('/emergency')"
        >
          <ion-icon :icon="warningOutline" slot="start" />
          <ion-label>Emergency</ion-label>
        </ion-item>

        <ion-item 
          button 
          :class="{ 'menu-item-active': $route.name === 'Profile' }"
          @click="navigateTo('/profile')"
        >
          <ion-icon :icon="personCircleOutline" slot="start" />
          <ion-label>Profile</ion-label>
        </ion-item>
      </ion-list>

      <ion-list>
        <ion-item button @click="handleLogout">
          <ion-icon :icon="logOutOutline" slot="start" color="danger" />
          <ion-label color="danger">Logout</ion-label>
        </ion-item>
      </ion-list>
    </ion-content>
  </ion-menu>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { menuController } from '@ionic/vue';
import { useAuthStore } from '../stores/auth';
import {
  IonMenu,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonList,
  IonItem,
  IonLabel,
  IonIcon,
} from '@ionic/vue';
import {
  homeOutline,
  calendarOutline,
  warningOutline,
  personCircleOutline,
  logOutOutline,
} from 'ionicons/icons';

const router = useRouter();
const authStore = useAuthStore();

function navigateTo(path: string) {
  menuController.close('main-menu');
  router.push(path);
}

async function handleLogout() {
  menuController.close('main-menu');
  await authStore.logout();
  router.push('/login');
}
</script>

<style scoped>
.menu-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
}

.menu-logo {
  font-size: 32px;
}

.menu-title h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.menu-title p {
  margin: 0;
  font-size: 12px;
  opacity: 0.8;
}

ion-item {
  --padding-start: 16px;
  --padding-end: 16px;
  --min-height: 56px;
}

.menu-item-active {
  --background: rgba(var(--ion-color-primary-rgb), 0.1);
  --color: var(--ion-color-primary);
  font-weight: 600;
}

.menu-item-active ion-icon {
  color: var(--ion-color-primary);
}
</style>

