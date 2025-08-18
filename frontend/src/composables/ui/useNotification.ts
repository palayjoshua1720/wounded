// src/composables/ui/useNotification.ts
export function useNotification() {
  function notify(title: string, options?: NotificationOptions) {
    if (typeof window === 'undefined' || !('Notification' in window)) {
      console.warn('Notifications are not supported in this environment.');
      return;
    }
    if (Notification.permission === 'granted') {
      new Notification(title, options);
    } else if (Notification.permission !== 'denied') {
      Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
          new Notification(title, options);
        }
      });
    }
  }
  return { notify };
} 