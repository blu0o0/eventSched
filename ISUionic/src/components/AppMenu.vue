<template>
  <ion-menu side="start" menu-id="main-menu" content-id="main-content">
    <div class="sidebar-container">
      <div class="sidebar-header">
        <div class="sidebar-brand">
          <img src="https://th.bing.com/th/id/OIP.YFWeW9_VhHAAEQFvvsJxhgAAAA?o=7&rm=3&rs=1&pid=ImgDetMain" alt="ISU Logo" class="sidebar-logo">
          <div class="sidebar-brand-text">
            <h5>Event Scheduling</h5>
            <small>ISU Santiago</small>
          </div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <ion-item 
          button 
          class="nav-item"
          :class="{ 'nav-active': $route.name === 'Home' }"
          @click="navigateTo('/home')"
        >
          <ion-icon :icon="speedometerOutline" slot="start" />
          <ion-label>Dashboard</ion-label>
        </ion-item>

        <div class="nav-section">Management</div>
        <ion-item 
          button 
          class="nav-item"
          :class="{ 'nav-active': $route.name === 'Reservations' || $route.name === 'CreateReservation' || $route.name === 'ReservationDetail' || $route.name === 'EditReservation' }"
          @click="navigateTo('/reservations')"
        >
          <ion-icon :icon="calendarOutline" slot="start" />
          <ion-label>Reservations</ion-label>
        </ion-item>

        <ion-item 
          button 
          class="nav-item"
          :class="{ 'nav-active': $route.name === 'EmergencyReports' || $route.name === 'CreateEmergency' || $route.name === 'EmergencyDetail' }"
          @click="navigateTo('/emergency')"
        >
          <ion-icon :icon="warningOutline" slot="start" />
          <ion-label>Emergency</ion-label>
        </ion-item>

        <div class="nav-section">Account</div>
        <ion-item 
          button 
          class="nav-item"
          :class="{ 'nav-active': $route.name === 'Profile' }"
          @click="navigateTo('/profile')"
        >
          <ion-icon :icon="personCircleOutline" slot="start" />
          <ion-label>Profile</ion-label>
        </ion-item>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ (authStore.user?.name || 'U')[0] }}</div>
          <div class="user-details">
            <p class="user-name">{{ authStore.user?.name || 'User' }}</p>
            <p class="user-role">{{ authStore.user?.role || 'User' }}</p>
          </div>
        </div>
        <ion-item button class="logout-item" @click="handleLogout">
          <ion-icon :icon="logOutOutline" slot="start" />
          <ion-label>Logout</ion-label>
        </ion-item>
      </div>
    </div>
  </ion-menu>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { menuController } from '@ionic/vue';
import { useAuthStore } from '../stores/auth';
import {
  IonMenu,
  IonItem,
  IonLabel,
  IonIcon,
} from '@ionic/vue';
import {
  speedometerOutline,
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
.sidebar-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: linear-gradient(180deg, #001829 0%, #002a45 100%);
}

.sidebar-header {
  padding: 28px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  color: #ffffff;
}

.sidebar-logo {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: contain;
  background: white;
  padding: 4px;
  box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
}

.sidebar-brand-text h5 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
  line-height: 1.3;
  letter-spacing: -0.3px;
}

.sidebar-brand-text small {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 16px 12px;
}

.nav-section {
  padding: 12px 12px 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: rgba(255, 255, 255, 0.5);
  margin-top: 8px;
}

.nav-item {
  --padding-start: 12px;
  --padding-end: 12px;
  --min-height: 48px;
  --background: transparent;
  --color: rgba(255, 255, 255, 0.85);
  --border-radius: 8px;
  margin: 3px 0;
  --ripple-color: rgba(255, 255, 255, 0.1);
  font-size: 14px;
  font-weight: 500;
}

.nav-item:hover {
  --background: rgba(44, 192, 160, 0.15);
  --color: #ffffff;
}

.nav-item.nav-active {
  --background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  --color: #ffffff;
  font-weight: 600;
}

.nav-item::part(native) {
  border-radius: 8px;
}

.nav-item ion-icon {
  color: rgba(255, 255, 255, 0.7);
  font-size: 20px;
}

.nav-item.nav-active ion-icon {
  color: #ffffff;
}

.sidebar-footer {
  flex-shrink: 0;
  padding: 16px 12px;
  background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.3) 30%, rgba(0, 0, 0, 0.4) 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  padding: 10px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 16px;
  box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
  flex-shrink: 0;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  color: #ffffff;
  font-size: 14px;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  letter-spacing: -0.2px;
}

.user-role {
  color: rgba(255, 255, 255, 0.7);
  font-size: 11px;
  margin: 4px 0 0 0;
  font-weight: 500;
  text-transform: capitalize;
}

.logout-item {
  --padding-start: 12px;
  --padding-end: 12px;
  --min-height: 44px;
  --background: rgba(239, 68, 68, 0.12);
  --color: #fca5a5;
  --border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
}

.logout-item:hover {
  --background: rgba(239, 68, 68, 0.2);
  --color: #fecaca;
}

.logout-item ion-icon {
  color: #fca5a5;
}
</style>