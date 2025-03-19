<template>
  <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
    <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
    Odeslat odpověď
  </button>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'SubmitButton',
  props: {
    submitting: {
      type: Boolean,
      required: true
    }
  }
})
</script>
