import { toastController } from '@ionic/vue';

export function useToast() {
  const show = async (message: string, color: 'success' | 'danger' | 'warning' | 'primary' = 'primary', duration: number = 3000) => {
    const toast = await toastController.create({
      message,
      color,
      duration,
      position: 'top',
    });
    await toast.present();
  };

  return {
    show,
  };
}