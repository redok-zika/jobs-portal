<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="card-text" v-html="sanitizedDescription"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import DOMPurify from 'dompurify'

const props = defineProps<{
  description: string
}>()

const sanitizedDescription = computed(() => DOMPurify.sanitize(props.description))
</script>
