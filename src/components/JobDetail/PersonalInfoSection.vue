<template>
  <div class="mb-4">
    <h6 class="mb-3">Osobní údaje</h6>

    <div class="mb-3">
      <label for="name" class="form-label">
        Jméno a příjmení <span class="text-danger">*</span>
      </label>
      <input
        type="text"
        class="form-control"
        id="name"
        :value="name"
        @input="$emit('update:name', ($event.target as HTMLInputElement).value)"
        :class="{ 'is-invalid': errors.name }"
        placeholder="Jan Novák"
        required
      />
      <div class="invalid-feedback">{{ errors.name }}</div>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label"> Email <span class="text-danger">*</span> </label>
      <input
        type="email"
        class="form-control"
        id="email"
        :value="email"
        @input="$emit('update:email', ($event.target as HTMLInputElement).value)"
        :class="{ 'is-invalid': errors.email }"
        required
      />
      <div class="invalid-feedback">{{ errors.email }}</div>
    </div>

    <div class="mb-3">
      <label for="phone" class="form-label"> Telefon <span class="text-danger">*</span> </label>
      <input
        type="tel"
        class="form-control"
        id="phone"
        :value="phone"
        @input="$emit('update:phone', ($event.target as HTMLInputElement).value)"
        :class="{ 'is-invalid': errors.phone }"
        required
      />
      <div class="invalid-feedback">{{ errors.phone }}</div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import type { FormErrors } from '../../types/forms'

export default defineComponent({
  name: 'PersonalInfoSection',
  props: {
    name: {
      type: String,
      required: true
    },
    email: {
      type: String,
      required: true
    },
    phone: {
      type: String,
      required: true
    },
    errors: {
      type: Object as () => FormErrors,
      required: true
    }
  },
  emits: ['update:name', 'update:email', 'update:phone']
})
</script>
