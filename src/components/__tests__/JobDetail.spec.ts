import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import JobDetail from '../JobDetail.vue'
import { useJobStore } from '../../stores/job'

const router = createRouter({
  history: createWebHistory(),
  routes: [{ path: '/jobs/:id', component: JobDetail }]
})

describe('JobDetail', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    router.push('/jobs/1')
  })

  it('displays job details', async () => {
    const store = useJobStore()
    store.currentJob = {
      job_id: 1,
      title: 'Test Job',
      description: 'Test Description'
    }

    const wrapper = mount(JobDetail, {
      global: {
        plugins: [router]
      }
    })

    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Test Job')
    expect(wrapper.text()).toContain('Test Description')
  })

  it('validates form before submission', async () => {
    const wrapper = mount(JobDetail, {
      global: {
        plugins: [router]
      }
    })

    await wrapper.find('form').trigger('submit')
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Vyplňte jméno')
    expect(wrapper.text()).toContain('Vyplňte email')
  })

  it('submits application successfully', async () => {
    const store = useJobStore()
    const submitSpy = vi.spyOn(store, 'submitApplication').mockResolvedValue({ id: '123' })

    const wrapper = mount(JobDetail, {
      global: {
        plugins: [router]
      }
    })

    await wrapper.find('#firstName').setValue('John')
    await wrapper.find('#lastName').setValue('Doe')
    await wrapper.find('#email').setValue('john@example.com')
    await wrapper.find('#phone').setValue('123456789')
    await wrapper.find('form').trigger('submit')

    expect(submitSpy).toHaveBeenCalledWith('1', {
      firstName: 'John',
      lastName: 'Doe',
      email: 'john@example.com',
      phone: '123456789',
      message: ''
    })

    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Vaše odpověď byla úspěšně odeslána')
  })

  it('handles API errors gracefully', async () => {
    const store = useJobStore()
    vi.spyOn(store, 'submitApplication').mockRejectedValue(new Error('API Error'))

    const wrapper = mount(JobDetail, {
      global: {
        plugins: [router]
      }
    })

    await wrapper.find('#firstName').setValue('John')
    await wrapper.find('#lastName').setValue('Doe')
    await wrapper.find('#email').setValue('john@example.com')
    await wrapper.find('#phone').setValue('123456789')
    await wrapper.find('form').trigger('submit')

    await wrapper.vm.$nextTick()
    expect(wrapper.find('.alert-danger').text()).toBe('API Error')
  })
})
