<template>
  <ion-chip :color="color" :outline="outline">
    <ion-label>{{ label }}</ion-label>
  </ion-chip>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { IonChip, IonLabel } from '@ionic/vue';

interface Props {
  status: 'pending' | 'approved' | 'rejected' | 'postponed' | 'open' | 'closed';
  outline?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  outline: false,
});

const color = computed(() => {
  switch (props.status) {
    case 'pending':
      return 'warning';
    case 'approved':
      return 'success';
    case 'rejected':
      return 'danger';
    case 'postponed':
      return 'warning';
    case 'open':
      return 'danger';
    case 'closed':
      return 'success';
    default:
      return 'medium';
  }
});

const label = computed(() => {
  return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});
</script>

