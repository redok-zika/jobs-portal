<template>
  <div class="mb-3">
    <div class="form-check">
      <input
        id="gdpr_agreement"
        class="form-check-input"
        type="checkbox"
        :checked="agreement"
        :class="{ 'is-invalid': error }"
        required
        @change="$emit('update:agreement', ($event.target as HTMLInputElement).checked)"
      />
      <label class="form-check-label" for="gdpr_agreement">
        <span class="text-danger">*</span>
        Souhlasím se zpracováním osobních údajů
      </label>
      <div class="invalid-feedback">{{ error }}</div>
    </div>
    <small class="text-muted d-block mt-2">
      <span class="text-danger">*</span> Označuje povinné pole
    </small>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  agreement: boolean
  error?: string
}>()

defineEmits<{
  (e: 'update:agreement', value: boolean): void
}>()
</script>
