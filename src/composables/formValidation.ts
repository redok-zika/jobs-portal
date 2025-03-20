import type { FormData, FormErrors } from '../types/forms'

interface ValidationResult {
  isValid: boolean
  validationErrors: FormErrors
}

interface ValidationRule {
  validate: (value: string) => boolean
  message: string
}

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/
const urlRegex = /^https?:\/\/.+/

const validationRules = {
  required: (fieldName: string): ValidationRule => ({
    validate: (value: string) => !!value?.trim(),
    message: `${fieldName} je povinné`
  }),
  email: {
    validate: (value: string) => emailRegex.test(value),
    message: 'Zadejte platný email'
  },
  phone: {
    validate: (value: string) => phoneRegex.test(value),
    message: 'Zadejte platné telefonní číslo'
  },
  url: {
    validate: (value: string) => !value || urlRegex.test(value),
    message: 'Zadejte platnou URL adresu'
  }
}

export function useFormValidation() {
  const validateField = (value: string, rules: ValidationRule[]): string | null => {
    for (const rule of rules) {
      if (!rule.validate(value)) {
        return rule.message
      }
    }
    return null
  }

  const validatePersonalInfo = (form: FormData): FormErrors => {
    const errors: FormErrors = {}

    const nameError = validateField(form.name, [validationRules.required('Jméno a příjmení')])
    if (nameError) errors.name = nameError

    if (form.email) {
      const emailError = validateField(form.email, [
        validationRules.required('Email'),
        validationRules.email
      ])
      if (emailError) errors.email = emailError
    }

    if (form.phone) {
      const phoneError = validateField(form.phone, [
        validationRules.required('Telefon'),
        validationRules.phone
      ])
      if (phoneError) errors.phone = phoneError
    }

    return errors
  }

  const validateSocialMedia = (form: FormData): FormErrors => {
    const errors: FormErrors = {}

    if (form.linkedin) {
      const linkedinError = validateField(form.linkedin, [validationRules.url])
      if (linkedinError) errors.linkedin = linkedinError
    }

    if (form.facebook) {
      const facebookError = validateField(form.facebook, [validationRules.url])
      if (facebookError) errors.facebook = facebookError
    }

    if (form.twitter) {
      const twitterError = validateField(form.twitter, [validationRules.url])
      if (twitterError) errors.twitter = twitterError
    }

    return errors
  }

  const validateSalary = (form: FormData): FormErrors => {
    const errors: FormErrors = {}

    if (form.salary?.amount < 0) {
      errors.salary_amount = 'Částka nemůže být záporná'
    }

    return errors
  }

  const validateGdpr = (form: FormData): FormErrors => {
    const errors: FormErrors = {}

    if (!form.gdpr_agreement) {
      errors.gdpr_agreement = 'Pro odeslání je nutný souhlas se zpracováním osobních údajů'
    }

    return errors
  }

  const validateForm = (form: FormData): ValidationResult => {
    const errors: FormErrors = {
      ...validatePersonalInfo(form),
      ...validateSocialMedia(form),
      ...validateSalary(form),
      ...validateGdpr(form)
    }

    return {
      isValid: Object.keys(errors).length === 0,
      validationErrors: errors
    }
  }

  return {
    validateForm,
    validateField
  }
}