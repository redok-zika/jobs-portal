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
  amount?: number
  type?: number
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
