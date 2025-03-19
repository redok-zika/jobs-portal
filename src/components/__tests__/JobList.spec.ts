import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import JobList from '../JobList.vue'
import { useJobStore } from '../../stores/job'

describe('JobList', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('displays loading state', () => {
    const wrapper = mount(JobList)
    expect(wrapper.find('.spinner-border').exists()).toBe(true)
  })

  it('displays jobs after loading', async () => {
    const store = useJobStore()
    store.jobs = [
      { job_id: 1, title: 'Job 1', description: 'Description 1' },
      { job_id: 2, title: 'Job 2', description: 'Description 2' }
    ]
    store.totalPages = 1

    const wrapper = mount(JobList)
    await wrapper.vm.$nextTick()

    expect(wrapper.findAll('.card')).toHaveLength(2)
    expect(wrapper.text()).toContain('Job 1')
    expect(wrapper.text()).toContain('Job 2')
  })

  it('handles pagination click', async () => {
    const store = useJobStore()
    store.totalPages = 3
    const fetchJobsSpy = vi.spyOn(store, 'fetchJobs').mockResolvedValue()

    const wrapper = mount(JobList)
    await wrapper.vm.$nextTick()

    const pageButtons = wrapper.findAll('.page-link')
    await pageButtons[1].trigger('click')

    expect(fetchJobsSpy).toHaveBeenCalledWith(2, 10)
  })

  it('displays error message on API failure', async () => {
    const store = useJobStore()
    vi.spyOn(store, 'fetchJobs').mockRejectedValue(new Error('API Error'))

    const wrapper = mount(JobList)
    await wrapper.vm.$nextTick()

    expect(wrapper.find('.alert-danger').exists()).toBe(true)
    expect(wrapper.find('.alert-danger').text()).toBe('API Error')
  })
})
