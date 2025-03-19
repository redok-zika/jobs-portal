import type { FormData, FormErrors } from '../types/forms'

export function useFormValidation() {
  const validateForm = (form: FormData): { isValid: boolean; validationErrors: FormErrors } => {
    const errors: FormErrors = {}
    let isValid = true
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/
    const urlRegex = /^https?:\/\/.+/

    // Required fields
    if (!form.name) {
      errors.name = 'Jméno a příjmení jsou povinná'
      isValid = false
    }

    if (!form.email) {
      errors.email = 'Email je povinný'
      isValid = false
    } else if (!emailRegex.test(form.email)) {
      errors.email = 'Zadejte platný email'
      isValid = false
    }

    if (!form.phone) {
      errors.phone = 'Telefon je povinný'
      isValid = false
    } else if (!phoneRegex.test(form.phone)) {
      errors.phone = 'Zadejte platné telefonní číslo'
      isValid = false
    }

    // Social media URLs
    if (form.linkedin && !urlRegex.test(form.linkedin)) {
      errors.linkedin = 'Zadejte platnou URL adresu'
      isValid = false
    }

    if (form.facebook && !urlRegex.test(form.facebook)) {
      errors.facebook = 'Zadejte platnou URL adresu'
      isValid = false
    }

    if (form.twitter && !urlRegex.test(form.twitter)) {
      errors.twitter = 'Zadejte platnou URL adresu'
      isValid = false
    }

    // Salary validation
    if (form.salary?.amount < 0) {
      errors.salary_amount = 'Částka nemůže být záporná'
      isValid = false
    }

    // GDPR agreement
    if (!form.gdpr_agreement) {
      errors.gdpr_agreement = 'Pro odeslání je nutný souhlas se zpracováním osobních údajů'
      isValid = false
    }

    return { isValid, validationErrors: errors }
  }

  return {
    validateForm
  }
}
