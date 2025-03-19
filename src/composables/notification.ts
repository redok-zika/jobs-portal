import { ref } from 'vue'

export interface NotificationOptions {
  title?: string
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration?: number
}

const notifications = ref<NotificationOptions[]>([])

export function useNotification() {
  const showNotification = (options: NotificationOptions) => {
    const notification = {
      title: options.title || 'Oznámení',
      message: options.message,
      type: options.type || 'info',
      duration: options.duration || 3000
    }

    notifications.value.push(notification)

    if (notification.duration > 0) {
      setTimeout(() => {
        removeNotification(notification)
      }, notification.duration)
    }
  }

  const removeNotification = (notification: NotificationOptions) => {
    const index = notifications.value.indexOf(notification)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  return {
    notifications,
    showNotification,
    removeNotification
  }
}
