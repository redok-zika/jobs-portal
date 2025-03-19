export interface Attachment {
  path?: string
  base64?: string
  filename: string
  type: number
}

export interface Salary {
  amount: number
  currency: string
  unit: string
  type: number
  note?: string
  visible: boolean
}

export interface FormData {
  name: string
  email: string
  phone: string
  message?: string
  cover_letter?: string
  linkedin?: string
  facebook?: string
  twitter?: string
  attachments: Attachment[]
  salary: Salary
  gdpr_agreement: boolean
}

export interface FormErrors {
  name?: string
  email?: string
  phone?: string
  linkedin?: string
  facebook?: string
  twitter?: string
  salary_amount?: string
  gdpr_agreement?: string
  [key: string]: string | undefined
}
