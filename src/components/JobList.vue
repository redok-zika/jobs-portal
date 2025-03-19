<template>
  <div class="container py-4">
    <div class="mb-4">
      <div class="d-flex align-items-center gap-3 mb-4">
        <el-icon size="32" class="text-primary">
          <Briefcase />
        </el-icon>
        <h1 class="h2 mb-0">Pracovní nabídky</h1>
      </div>

      <el-pagination
        v-if="totalPages > 1"
        v-model:current-page="currentPage"
        :page-count="totalPages"
        :background="true"
        layout="total, prev, pager, next"
        @current-change="loadPage"
        class="mb-4 d-flex justify-content-center"
      />

      <el-skeleton :loading="loading" animated>
        <template #template>
          <div class="row">
            <div v-for="i in 4" :key="i" class="col-md-6 mb-4">
              <el-card>
                <el-skeleton-item variant="h3" style="width: 50%" />
                <el-skeleton-item variant="text" style="margin: 16px 0" />
                <el-skeleton-item variant="text" style="width: 60%" />
              </el-card>
            </div>
          </div>
        </template>
        <template #default>
          <div v-if="error" class="alert alert-danger" role="alert">
            {{ error }}
          </div>

          <div v-else>
            <div class="row">
              <div v-for="job in jobs" :key="job.job_id" class="col-md-6 mb-4">
                <el-card class="h-100 job-card" shadow="hover">
                  <template #header>
                    <h5 class="mb-0 text-primary">{{ job.title }}</h5>
                  </template>

                  <div class="mb-3">
                    <p class="text-secondary">
                      {{ stripHtml(job.description).substring(0, 200) }}...
                    </p>
                  </div>

                  <el-descriptions :column="1" size="small" class="mb-3">
                    <el-descriptions-item v-if="job.workfields?.length" label="Obor">
                      <el-tag size="small" type="info" effect="plain">
                        {{ job.workfields.map(w => w.name).join(', ') }}
                      </el-tag>
                    </el-descriptions-item>

                    <el-descriptions-item v-if="job.addresses?.length" label="Lokalita">
                      <el-tag size="small" type="success" effect="plain">
                        <el-icon class="me-1"><Location /></el-icon>
                        {{ job.addresses[0].city }}, {{ job.addresses[0].region }}
                      </el-tag>
                    </el-descriptions-item>

                    <el-descriptions-item v-if="job.salary?.visible" label="Plat">
                      <el-tag size="small" type="warning" effect="plain">
                        <el-icon class="me-1"><Money /></el-icon>
                        {{ formatSalary(job.salary) }}
                      </el-tag>
                    </el-descriptions-item>

                    <el-descriptions-item v-if="job.contact?.name" label="Kontakt">
                      <el-tag size="small" type="info" effect="plain">
                        <el-icon class="me-1"><User /></el-icon>
                        {{ job.contact.name }}
                      </el-tag>
                    </el-descriptions-item>
                  </el-descriptions>

                  <router-link
                    :to="{ name: 'job-detail', params: { id: job.job_id.toString() } }"
                    class="text-decoration-none"
                  >
                    <el-button type="primary" class="w-100" :icon="ArrowRight">
                      Zobrazit detail
                    </el-button>
                  </router-link>
                </el-card>
              </div>
            </div>

            <el-pagination
              v-if="totalPages > 1"
              v-model:current-page="currentPage"
              :page-count="totalPages"
              :background="true"
              layout="total, prev, pager, next"
              @current-change="loadPage"
              class="mt-4 d-flex justify-content-center"
            />
          </div>
        </template>
      </el-skeleton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { ArrowRight, Briefcase, Location, Money, User } from '@element-plus/icons-vue'
import { useJobStore, type Salary } from '../stores/job'

const stripHtml = (html: string = '') => {
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return doc.body.textContent || ''
}

const ITEMS_PER_PAGE = 10

const store = useJobStore()
const currentPage = ref(1)
const initialized = ref(false)

const formatSalary = (salary: Salary): string => {
  const parts = []

  if (salary.is_min_visible) {
    parts.push(`od ${salary.min?.toLocaleString()} ${salary.currency}`)
  }

  if (salary.is_max_visible) {
    parts.push(`do ${salary.max?.toLocaleString()} ${salary.currency}`)
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

const loadPage = async (page: number) => {
  try {
    await store.fetchJobs(page, ITEMS_PER_PAGE)
    if (store.jobs.length === 0) {
      store.error = 'Žádné pracovní nabídky nebyly nalezeny'
    } else {
      currentPage.value = page
      initialized.value = true
    }
  } catch (e) {
    store.error = e instanceof Error ? e.message : 'Nepodařilo se načíst nabídky'
  }
}

onMounted(async () => {
  if (!initialized.value) {
    await loadPage(1)
  }
})

const jobs = computed(() => store.jobs)
const totalPages = computed(() => store.totalPages)
const loading = computed(() => store.loading)
const error = computed(() => store.error)
</script>

<style scoped>
.job-card {
  transition: transform 0.2s;
}

.job-card:hover {
  transform: translateY(-4px);
}

:deep(.el-descriptions__label) {
  color: #606266;
  font-weight: 500;
}

:deep(.el-tag) {
  border-radius: 4px;
}
</style>
