<template>
  <form @submit.prevent="handleSubmit" novalidate>
    <PersonalInfoSection
      v-model:name="form.name"
      v-model:email="form.email"
      v-model:phone="form.phone"
      :errors="errors"
    />

    <SocialMediaSection
      v-model:linkedin="form.linkedin"
      v-model:facebook="form.facebook"
      v-model:twitter="form.twitter"
    />

    <div class="mb-3">
      <label for="cover_letter" class="form-label">Zpráva</label>
      <textarea
        class="form-control"
        id="cover_letter"
        v-model="form.cover_letter"
        rows="4"
        placeholder="Průvodní dopis"
      ></textarea>
    </div>

    <SalarySection v-model:salary="form.salary" :errors="errors" />
    <AttachmentsSection v-model:attachments="form.attachments" @error="handleError" />

    <GdprAgreement v-model:agreement="form.gdpr_agreement" :error="errors.gdpr_agreement" />

    <SubmitButton :submitting="submitting" />
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useJobStore } from '../../stores/job'
import { useNotification } from '../../composables/notification'
import PersonalInfoSection from './PersonalInfoSection.vue'
import SocialMediaSection from './SocialMediaSection.vue'
import SalarySection from './SalarySection.vue'
import AttachmentsSection from './AttachmentsSection.vue'
import GdprAgreement from './GdprAgreement.vue'
import SubmitButton from './SubmitButton.vue'
import type { FormData, FormErrors } from '../../types/forms'
import { useFormValidation } from '../../composables/formValidation'

const props = defineProps<{
  jobId: string
}>()

const emit = defineEmits<{
  (e: 'success'): void
}>()

const store = useJobStore()
const { showNotification } = useNotification()
const { validateForm } = useFormValidation()

const form = ref<FormData>({
  name: '',
  email: '',
  phone: '',
  cover_letter: '',
  linkedin: '',
  facebook: '',
  twitter: '',
  attachments: [],
  salary: {
    amount: 0,
    currency: 'CZK',
    unit: 'month',
    type: 0,
    note: '',
    visible: true
  },
  gdpr_agreement: false
})

const errors = ref<FormErrors>({})
const submitting = ref(false)
const submitSuccess = ref(false)

const handleError = (message: string) => {
  showNotification({
    title: 'Chyba',
    message,
    type: 'error'
  })
}

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    phone: '',
    cover_letter: '',
    linkedin: '',
    facebook: '',
    twitter: '',
    attachments: [],
    salary: {
      amount: 0,
      currency: 'CZK',
      unit: 'month',
      type: 0,
      note: '',
      visible: true
    },
    gdpr_agreement: false
  }
}

const handleSubmit = async () => {
  const { isValid, validationErrors } = validateForm(form.value)
  errors.value = validationErrors

  if (!isValid) return

  submitting.value = true
  submitSuccess.value = false

  try {
    await store.submitApplication(props.jobId, form.value)
    submitSuccess.value = true
    resetForm()
    emit('success')
  } catch (e) {
    handleError(e instanceof Error ? e.message : 'Nepodařilo se odeslat odpověď')
  } finally {
    submitting.value = false
  }
}
</script>
