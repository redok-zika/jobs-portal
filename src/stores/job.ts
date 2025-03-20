import { defineStore } from 'pinia'

import type { ApplicationData } from '../types/api'

export interface Address {
  id?: number
  city: string
  postcode?: number
  street?: string
  region: string
  state?: string
  is_primary?: boolean
}

export interface Salary {
  is_range?: boolean
  is_min_visible?: boolean
  is_max_visible?: boolean
  min?: number
  max?: number
  currency: string
  unit: string
  visible: boolean
  note?: string
}

export interface Job {
  job_id: number
  title: string
  description: string
  active?: boolean
  addresses?: Address[]
  salary?: Salary
  workfields?: Array<{
    id: number
    name: string
  }>
  contact?: {
    name: string
    email: string
    phone: string
  }
}

export const useJobStore = defineStore('job', {
  state: () => ({
    jobs: [] as Job[],
    currentJob: null as Job | null,
    totalPages: 1,
    loading: false,
    error: null as string | null
  }),

  actions: {
    async fetchJobs(page: number, limit = 10) {
      this.loading = true
      this.error = null

      try {
        const response = await fetch(`/api/jobs?page=${page}&limit=${limit}`, {
          headers: {
            Accept: 'application/json'
          }
        })

        if (!response.ok) {
          throw new Error('Nepodařilo se načíst nabídky')
        }

        const data = await response.json()

        // Reset state before processing new data
        this.jobs = []
        this.totalPages = 1

        // Handle API response
        if (data && typeof data === 'object') {
          // Check for payload wrapper format
          if (!data.payload || !Array.isArray(data.payload)) {
            throw new Error('Neplatná struktura odpovědi z API')
          }

          this.jobs = data.payload

          // Calculate total pages
          const total = data.meta?.entries_total || data.payload.length
          this.totalPages = Math.max(1, Math.ceil(total / limit))
        } else {
          throw new Error('Neplatná struktura odpovědi z API')
        }
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Nepodařilo se načíst nabídky'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchJob(id: string) {
      this.currentJob = null
      this.loading = true
      this.error = null

      try {
        const response = await fetch(`/api/jobs/${id}`, {
          headers: {
            Accept: 'application/json'
          }
        })

        if (!response.ok) {
          throw new Error('Nepodařilo se načíst detail nabídky')
        }

        const data = await response.json()

        if (!data || typeof data !== 'object') {
          throw new Error('Neplatná odpověď z API')
        }

        if (data.payload) {
          if (!data.payload.job_id) {
            throw new Error('Neplatná struktura odpovědi z API')
          }
          this.currentJob = data.payload
        } else {
          if (!data.job_id) {
            throw new Error('Neplatná struktura odpovědi z API')
          }
          this.currentJob = data
        }
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Nepodařilo se načíst detail nabídky'
        throw e
      } finally {
        this.loading = false
      }
    },

    async submitApplication(jobId: string, data: ApplicationData) {
      const formData: ApplicationData & { salary?: Salary } = {
        job_id: parseInt(jobId, 10),
        name: data.name.trim(),
        email: data.email.trim(),
        phone: data.phone.trim(),
        cover_letter: data.cover_letter?.trim() || '',
        gdpr_agreement: data.gdpr_agreement,
        linkedin: data.linkedin?.trim() || null,
        facebook: data.facebook?.trim() || null,
        twitter: data.twitter?.trim() || null,
        attachments: data.attachments,
        source: 'web'
      }

      if (data.salary) {
        formData.salary = {
          amount: data.salary.amount,
          currency: data.salary.currency || 'CZK',
          unit: data.salary.unit || 'month',
          type: data.salary.type || 0,
          note: data.salary.note?.trim(),
          visible: true
        }
      }

      const response = await fetch(`/api/jobs/${jobId}/apply`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify(formData)
      })

      const responseData = await response.json()

      if (!response.ok) {
        let errorMessage =
          responseData.error ||
          responseData.error?.message ||
          responseData.meta?.message ||
          'Nepodařilo se odeslat odpověď'

        if (responseData.meta?.code === 'api.error.validation') {
          errorMessage = 'Zkontrolujte prosím všechna povinná pole'
        }

        throw new Error(errorMessage)
      }

      return responseData
    }
  }
})
