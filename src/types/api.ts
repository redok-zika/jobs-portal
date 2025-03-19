export interface ApplicationData {
  job_id?: number
  name: string
  email: string
  phone: string
  cover_letter?: string
  gdpr_agreement: boolean
  linkedin?: string | null
  facebook?: string | null
  twitter?: string | null
  source?: string
  attachments?: Array<{
    path?: string
    base64?: string
    filename: string
    type: number
  }>
  salary?: {
    amount: number
    currency: string
    unit: string
    type: number
    note?: string
    visible: boolean
  }
}
