<template>
  <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
    <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
    Odeslat odpověď
  </button>
</template>

<script lang="ts" setup>
defineProps<{ submitting: boolean }>()
</script>
