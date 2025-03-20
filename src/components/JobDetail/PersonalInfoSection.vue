<template>
  <div class="mb-4">
    <h6 class="mb-3">Osobní údaje</h6>

    <div class="mb-3">
      <label for="name" class="form-label">
        Jméno a příjmení <span class="text-danger">*</span>
      </label>
      <input
        id="name"
        type="text"
        class="form-control"
        :value="name"
        :class="{ 'is-invalid': errors.name }"
        placeholder="Jan Novák"
        required
        @input="$emit('update:name', ($event.target as HTMLInputElement).value)"
      />
      <div class="invalid-feedback">{{ errors.name }}</div>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label"> Email </label>
      <input
        id="email"
        type="email"
        class="form-control"
        :value="email"
        :class="{ 'is-invalid': errors.email }"
        @input="$emit('update:email', ($event.target as HTMLInputElement).value)"
      />
      <div class="invalid-feedback">{{ errors.email }}</div>
    </div>

    <div class="mb-3">
      <label for="phone" class="form-label"> Telefon </label>
      <input
        id="phone"
        type="tel"
        class="form-control"
        :value="phone"
        :class="{ 'is-invalid': errors.phone }"
        @input="$emit('update:phone', ($event.target as HTMLInputElement).value)"
      />
      <div class="invalid-feedback">{{ errors.phone }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { FormErrors } from '../../types/forms'

defineProps<{
  name: string
  email: string
  phone: string
  errors: FormErrors
}>()

defineEmits<{
  (e: 'update:name', value: string): void
  (e: 'update:email', value: string): void
  (e: 'update:phone', value: string): void
}>()
</script>
