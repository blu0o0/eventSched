<template>
  <div class="loading-container">
    <ion-spinner :name="spinnerName" :color="color"></ion-spinner>
    <p v-if="message" class="loading-message">{{ message }}</p>
  </div>
</template>

<script setup lang="ts">
import { IonSpinner } from '@ionic/vue';

interface Props {
  message?: string;
  spinnerName?: 'bubbles' | 'circles' | 'circular' | 'crescent' | 'dots' | 'lines' | 'lines-small' | null;
  color?: string;
}

withDefaults(defineProps<Props>(), {
  message: '',
  spinnerName: 'crescent',
  color: 'primary',
});
</script>

<style scoped>
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  min-height: 200px;
}

.loading-message {
  margin-top: 1rem;
  color: var(--ion-color-medium);
  font-size: 0.9rem;
}
</style>

