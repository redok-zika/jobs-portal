<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
      <router-link class="navbar-brand" to="/">Recruitis Job Board</router-link>
    </div>
  </nav>

  <div class="container">
    <router-view></router-view>
  </div>

  <Notification
    v-for="(notification, index) in notifications"
    :key="index"
    v-bind="notification"
    @close="removeNotification(notification)"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import { useNotification } from './composables/notification'
import Notification from './components/Notification.vue'

export default defineComponent({
  name: 'App',
  components: {
    Notification
  },
  setup() {
    const { notifications, removeNotification } = useNotification()

    return {
      notifications,
      removeNotification
    }
  }
})
</script>
