<template>
  <div class="mb-3">
    <div class="form-check">
      <input
        class="form-check-input"
        type="checkbox"
        id="gdpr_agreement"
        :checked="agreement"
        @change="$emit('update:agreement', ($event.target as HTMLInputElement).checked)"
        :class="{ 'is-invalid': error }"
        required
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

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'GdprAgreement',
  props: {
    agreement: {
      type: Boolean,
      required: true
    },
    error: {
      type: String,
      default: ''
    }
  },
  emits: ['update:agreement']
})
</script>
