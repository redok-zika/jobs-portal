<template>
  <div class="card mb-3">
    <div class="card-body">
      <h6 class="card-subtitle mb-3">Platové požadavky</h6>

      <div class="row g-3">
        <div class="col-md-6">
          <label for="salary_amount" class="form-label">Částka</label>
          <input
            id="salary_amount"
            type="number"
            class="form-control"
            :value="salary.amount"
            min="0"
            :class="{ 'is-invalid': errors.salary_amount }"
            @input="updateAmount(($event.target as HTMLInputElement).value)"
          />
          <div class="invalid-feedback">{{ errors.salary_amount }}</div>
        </div>

        <div class="col-md-6">
          <label for="salary_currency" class="form-label">Měna</label>
          <select
            id="salary_currency"
            class="form-select"
            :value="salary.currency"
            @change="updateCurrency(($event.target as HTMLSelectElement).value)"
          >
            <option v-for="currency in currencies" :key="currency" :value="currency">
              {{ currency }}
            </option>
          </select>
        </div>

        <div class="col-md-6">
          <label for="salary_unit" class="form-label">Jednotka</label>
          <select
            id="salary_unit"
            class="form-select"
            :value="salary.unit"
            @change="updateUnit(($event.target as HTMLSelectElement).value)"
          >
            <option v-for="(label, value) in units" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>

        <div class="col-md-6">
          <label for="salary_type" class="form-label">Typ úvazku</label>
          <select
            id="salary_type"
            class="form-select"
            :value="salary.type"
            @change="updateType(($event.target as HTMLSelectElement).value)"
          >
            <option v-for="(label, value) in types" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>

        <div class="col-12">
          <label for="salary_note" class="form-label">Poznámka k platu</label>
          <input
            id="salary_note"
            type="text"
            class="form-control"
            :value="salary.note"
            placeholder="Např. včetně bonusů"
            @input="updateNote(($event.target as HTMLInputElement).value)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Salary } from '../../types/job'
import type { FormErrors } from '../../types/forms'

const props = defineProps<{
  salary: Salary
  errors: FormErrors
}>()

const emit = defineEmits<{
  (e: 'update:salary', value: Salary): void
}>()

const currencies = ['CZK', 'EUR', 'USD', 'BGN', 'RON', 'HUF']

const units = {
  month: 'Měsíčně',
  manday: 'Za člověkoden',
  hour: 'Za hodinu',
  year: 'Ročně'
}

const types = {
  0: 'Plný úvazek',
  1: 'Zkrácený úvazek',
  2: 'Živnost',
  3: 'Práce přes internet',
  4: 'Práce z domova',
  5: 'Krátkodobá práce',
  6: 'Brigáda'
}

const updateSalary = (updates: Partial<Salary>) => {
  emit('update:salary', { ...props.salary, ...updates })
}

const updateAmount = (value: string) => {
  updateSalary({ amount: parseInt(value) || 0 })
}

const updateCurrency = (value: string) => {
  updateSalary({ currency: value })
}

const updateUnit = (value: string) => {
  updateSalary({ unit: value })
}

const updateType = (value: string) => {
  updateSalary({ type: parseInt(value) })
}

const updateNote = (value: string) => {
  updateSalary({ note: value })
}
</script>
