<template>
  <Teleport to="body">
    <el-notification
      v-for="alert in alerts"
      :key="alert.id"
      :title="alert.title"
      :message="alert.message"
      :type="alert.type"
      :duration="duration"
      @close="removeAlert(alert.id)"
    />
  </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface AlertItem {
  id: number
  title?: string
  message: string
  type: 'success' | 'warning' | 'error' | 'info'
}

const props = withDefaults(
  defineProps<{
    title?: string
    message: string
    type?: 'success' | 'error' | 'warning' | 'info'
    duration?: number
  }>(),
  {
    title: '',
    type: 'info',
    duration: 3000
  }
)

const emit = defineEmits<{
  (e: 'close'): void
}>()

const alerts = ref<AlertItem[]>([])
let nextId = 1

const addAlert = () => {
  const alert: AlertItem = {
    id: nextId++,
    title: props.title,
    message: props.message,
    type: props.type
  }
  alerts.value.push(alert)

  if (props.duration > 0) {
    setTimeout(() => {
      removeAlert(alert.id)
    }, props.duration)
  }
}

const removeAlert = (id: number) => {
  const index = alerts.value.findIndex(alert => alert.id === id)
  if (index > -1) {
    alerts.value.splice(index, 1)
    if (alerts.value.length === 0) {
      emit('close')
    }
  }
}

onMounted(() => {
  addAlert()
})
</script>
