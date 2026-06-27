<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Profile</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <div class="profile-container">
        <ion-card>
          <ion-card-content class="profile-header">
            <div class="avatar">
              <ion-icon :icon="personCircleOutline" />
            </div>
            <h2>{{ user?.name }}</h2>
            <p>{{ user?.email }}</p>
            <ion-chip color="primary">
              <ion-label>{{ formatRole(user?.role) }}</ion-label>
            </ion-chip>
          </ion-card-content>
        </ion-card>

        <ion-list>
          <ion-item button @click="handleEditName" detail>
            <ion-icon :icon="personOutline" slot="start" />
            <ion-label>
              <h3>Name</h3>
              <p>{{ user?.name }}</p>
            </ion-label>
            <ion-icon :icon="createOutline" slot="end" />
          </ion-item>

          <ion-item>
            <ion-icon :icon="mailOutline" slot="start" />
            <ion-label>
              <h3>Email</h3>
              <p>{{ user?.email }}</p>
            </ion-label>
          </ion-item>

          <ion-item>
            <ion-icon :icon="shieldCheckmarkOutline" slot="start" />
            <ion-label>
              <h3>Role</h3>
              <p>{{ formatRole(user?.role) }}</p>
            </ion-label>
          </ion-item>
        </ion-list>

        <div class="actions">
          <ion-button expand="block" color="danger" @click="handleLogout">
            <ion-icon :icon="logOutOutline" slot="start" />
            Logout
          </ion-button>
        </div>

        <div class="app-info">
          <p>App Version: 1.0.0</p>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonCard,
  IonCardContent,
  IonList,
  IonMenuButton,
  IonButtons,
  IonItem,
  IonLabel,
  IonIcon,
  IonButton,
  IonChip,
  alertController,
} from '@ionic/vue';
import {
  personCircleOutline,
  personOutline,
  mailOutline,
  shieldCheckmarkOutline,
  logOutOutline,
  createOutline,
} from 'ionicons/icons';
import { useAuthStore } from '../stores/auth';
import { useAuth } from '../composables/useAuth';
import { useToast } from '../composables/useToast';

const router = useRouter();
const authStore = useAuthStore();
const { logout } = useAuth();
const { show: showToast } = useToast();
const user = computed(() => authStore.user);
const isUpdatingName = ref(false);

function formatRole(role?: string): string {
  if (!role) return 'General User';
  return role
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

async function handleEditName() {
  const alert = await alertController.create({
    header: 'Change Name',
    inputs: [
      {
        name: 'name',
        type: 'text',
        placeholder: 'Enter your new name',
        value: user.value?.name || '',
        attributes: {
          maxlength: 255,
        },
      },
    ],
    buttons: [
      {
        text: 'Cancel',
        role: 'cancel',
      },
      {
        text: 'Save',
        handler: async (data) => {
          if (!data.name || !data.name.trim()) {
            return false;
          }
          try {
            isUpdatingName.value = true;
            await authStore.updateName(data.name.trim());
            showToast('Name updated successfully!', 'success');
          } catch (error: any) {
            const message = error?.response?.data?.message || 'Failed to update name. Please try again.';
            showToast(message, 'danger');
          } finally {
            isUpdatingName.value = false;
          }
        },
      },
    ],
  });

  await alert.present();
}

async function handleLogout() {
  const alert = await alertController.create({
    header: 'Logout',
    message: 'Are you sure you want to logout?',
    buttons: [
      {
        text: 'Cancel',
        role: 'cancel',
      },
      {
        text: 'Logout',
        role: 'destructive',
        handler: async () => {
          await logout();
        },
      },
    ],
  });

  await alert.present();
}
</script>

<style scoped>
.profile-container {
  padding: 1rem;
}

.profile-header {
  text-align: center;
  padding: 2rem 1rem;
}

.avatar {
  margin-bottom: 1rem;
}

.avatar ion-icon {
  font-size: 5rem;
  color: var(--ion-color-primary);
}

.profile-header h2 {
  margin: 0.5rem 0;
  font-size: 1.5rem;
  color: var(--ion-color-dark);
}

.profile-header p {
  margin: 0.5rem 0;
  color: var(--ion-color-medium);
}

.actions {
  margin-top: 2rem;
  padding: 0 1rem;
}

.app-info {
  text-align: center;
  margin-top: 2rem;
  padding: 1rem;
  color: var(--ion-color-medium);
  font-size: 0.9rem;
}

ion-item {
  --padding-start: 1rem;
}
</style>