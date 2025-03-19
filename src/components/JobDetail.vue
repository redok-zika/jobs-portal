<template>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <router-link
      :to="{ name: 'job-list' }"
      class="btn btn-outline-primary d-flex align-items-center gap-2"
    >
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M19 12H5M5 12L12 19M5 12L12 5"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
      Zpět na seznam
    </router-link>
  </div>

  <LoadingSpinner v-if="loading" />

  <div v-else-if="error" class="alert alert-danger" role="alert">
    {{ error }}
  </div>

  <div v-else-if="currentJob" class="row">
    <div class="col-md-8">
      <JobHeader :job="currentJob" />
      <JobDescription :description="currentJob.description" />
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-4">Odpovědět na nabídku</h5>
          <ApplicationForm :job-id="jobId" @success="handleApplicationSuccess" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useJobStore } from '../stores/job'
import LoadingSpinner from './common/LoadingSpinner.vue'
import JobHeader from './JobDetail/JobHeader.vue'
import JobDescription from './JobDetail/JobDescription.vue'
import ApplicationForm from './JobDetail/ApplicationForm.vue'
import { useNotification } from '../composables/notification'

const route = useRoute()
const store = useJobStore()
const { showNotification } = useNotification()

const jobId = computed(() => route.params.id as string)
const currentJob = computed(() => store.currentJob)
const localError = ref('')

const handleApplicationSuccess = () => {
  showNotification({
    title: 'Úspěch',
    message: 'Vaše odpověď byla úspěšně odeslána',
    type: 'success'
  })
}

onMounted(async () => {
  try {
    await store.fetchJob(jobId.value)
  } catch (e) {
    localError.value = e instanceof Error ? e.message : 'Nepodařilo se načíst detail nabídky'
  }
})

const loading = computed(() => store.loading)
const error = computed(() => store.error || localError.value)
</script>
