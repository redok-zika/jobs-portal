<template>
  <h1 class="mb-4">{{ job.title }}</h1>

  <div class="d-flex gap-4 mb-4">
    <div v-if="job.addresses?.length">
      <small class="text-muted d-flex align-items-center">
        <i class="me-1">📍</i>
        {{ job.addresses[0].city }}, {{ job.addresses[0].region }}
      </small>
    </div>

    <div v-if="job.salary?.visible">
      <small class="text-muted d-flex align-items-center">
        <i class="me-1">💰</i>
        {{ formatSalary(job.salary) }}
      </small>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Job, Salary } from '../../types/job'

defineProps<{
  job: Job
}>()

const formatSalary = (salary: Salary): string => {
  const parts = []

  if (salary.is_min_visible && salary.min !== undefined) {
    parts.push(`od ${salary.min.toLocaleString()} ${salary.currency}`)
  }

  if (salary.is_max_visible && salary.max !== undefined) {
    parts.push(`do ${salary.max.toLocaleString()} ${salary.currency}`)
  }

  let result = parts.join(' ')
  if (salary.unit === 'month') {
    result += ' / měsíc'
  }

  if (salary.note) {
    result += ` (${salary.note})`
  }

  return result || 'Mzda dle dohody'
}
</script>
