import { beforeEach, describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import JobDetail from '../JobDetail.vue'
import { useJobStore } from '../../stores/job'
import ElementPlus from 'element-plus'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/', name: 'job-list', component: { template: '<div>Job List</div>' } },
    { path: '/jobs/:id', name: 'job-detail', component: JobDetail }
  ]
})

describe('JobDetail', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('displays job details', async () => {
    const store = useJobStore()
    store.currentJob = {
      job_id: 1,
      title: 'Test Job',
      description: 'Test Description'
    }

    await router.push('/jobs/431912')

    const wrapper = mount(JobDetail, {
      global: {
        plugins: [router, ElementPlus]
      }
    })

    expect(wrapper.text()).toContain('Test Job')
    expect(wrapper.text()).toContain('Test Description')
  })
})
