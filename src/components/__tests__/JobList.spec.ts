import { beforeEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import JobList from '../JobList.vue'
import { useJobStore } from '../../stores/job'
import ElementPlus from 'element-plus'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/', name: 'job-list', component: JobList },
    { path: '/jobs/:id', name: 'job-detail', component: { template: '<div>Job Detail</div>' } }
  ]
})

describe('JobList', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    router.push('/')
    vi.clearAllMocks()
  })

  it('displays jobs after loading', async () => {
    const store = useJobStore()
    store.jobs = [
      { job_id: 1, title: 'Job 1', description: 'Description 1' },
      { job_id: 2, title: 'Job 2', description: 'Description 2' }
    ]
    store.totalPages = 1

    const wrapper = mount(JobList, {
      global: {
        plugins: [router, ElementPlus]
      }
    })

    expect(wrapper.findAll('.job-card')).toHaveLength(2)
    expect(wrapper.text()).toContain('Job 1')
    expect(wrapper.text()).toContain('Job 2')
  })

  it('displays error message on API failure', async () => {
    const store = useJobStore()
    vi.spyOn(store, 'fetchJobs').mockRejectedValue(new Error('API Error'))
    store.error = 'API Error'

    const wrapper = mount(JobList, {
      global: {
        plugins: [router, ElementPlus]
      }
    })

    expect(wrapper.find('.alert-danger').exists()).toBe(true)
    expect(wrapper.find('.alert-danger').text()).toBe('API Error')
  })
})
