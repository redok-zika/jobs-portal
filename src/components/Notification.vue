<template>
  <Teleport to="body">
    <div v-if="show" class="notification-wrapper">
      <div class="toast show" :class="type" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">{{ title }}</strong>
          <button type="button" class="btn-close" aria-label="Close" @click="hide"></button>
        </div>
        <div class="toast-body">
          {{ message }}
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import { useTimeoutFn } from '@vueuse/core'

export default defineComponent({
  name: 'Notification',
  props: {
    title: {
      type: String,
      default: 'Oznámení'
    },
    message: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: 'success',
      validator: (value: string) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    duration: {
      type: Number,
      default: 5000
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const show = ref(true)

    const hide = () => {
      show.value = false
      emit('close')
    }

    if (props.duration > 0) {
      useTimeoutFn(hide, props.duration)
    }

    return {
      show,
      hide
    }
  }
})
</script>

<style scoped>
.notification-wrapper {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1050;
}

.toast {
  min-width: 300px;
}

.toast.success .toast-header {
  background-color: #d4edda;
  color: #155724;
}

.toast.error .toast-header {
  background-color: #f8d7da;
  color: #721c24;
}

.toast.warning .toast-header {
  background-color: #fff3cd;
  color: #856404;
}

.toast.info .toast-header {
  background-color: #cce5ff;
  color: #004085;
}
</style>
