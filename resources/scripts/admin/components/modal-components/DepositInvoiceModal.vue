<template>
  <BaseModal :show="modalActive" @close="closeModal">
    <template #header>
      <div class="flex justify-between w-full">
        {{ $t('estimates.create_deposit_invoice') }}
        <BaseIcon
          name="XIcon"
          class="h-6 w-6 text-gray-500 cursor-pointer"
          @click="closeModal"
        />
      </div>
    </template>

    <div class="px-8 py-6">
      <!-- Existing deposits summary -->
      <div
        v-if="depositInvoices.length > 0"
        class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200"
      >
        <h4 class="text-sm font-semibold text-gray-700 mb-3">
          {{ $t('estimates.existing_deposits') }}
        </h4>
        <div
          v-for="deposit in depositInvoices"
          :key="deposit.id"
          class="flex justify-between items-center text-sm py-1"
        >
          <span class="text-gray-600">
            {{ deposit.invoice_number }}
            <span v-if="deposit.deposit_percentage" class="text-gray-400">
              ({{ deposit.deposit_percentage }}%)
            </span>
          </span>
          <BaseFormatMoney
            :amount="deposit.total"
            :currency="estimateCurrency"
          />
        </div>
        <hr class="my-3 border-gray-200" />
        <div class="flex justify-between items-center text-sm font-semibold">
          <span class="text-gray-700">
            {{ $t('estimates.remaining_amount') }}
          </span>
          <BaseFormatMoney
            :amount="remainingAmount"
            :currency="estimateCurrency"
          />
        </div>
      </div>

      <BaseInputGrid layout="one-column">
        <!-- Deposit type -->
        <BaseInputGroup
          :label="$t('estimates.deposit_type')"
          required
        >
          <div class="flex gap-4 mt-2">
            <label class="flex items-center cursor-pointer">
              <input
                v-model="formData.deposit_type"
                type="radio"
                value="percentage"
                class="form-radio text-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">
                {{ $t('estimates.percentage') }}
              </span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input
                v-model="formData.deposit_type"
                type="radio"
                value="fixed"
                class="form-radio text-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">
                {{ $t('estimates.fixed_amount') }}
              </span>
            </label>
          </div>
        </BaseInputGroup>

        <!-- Value input -->
        <BaseInputGroup
          :label="
            formData.deposit_type === 'percentage'
              ? $t('estimates.percentage_value')
              : $t('estimates.deposit_amount')
          "
          required
          :error="inputError"
        >
          <div class="relative">
            <BaseInput
              v-if="formData.deposit_type === 'percentage'"
              v-model="formData.deposit_value"
              type="number"
              min="0.01"
              max="100"
              step="0.01"
              class="mt-1"
            />
            <BaseInput
              v-else
              v-model="formData.deposit_value"
              type="number"
              min="1"
              step="1"
              class="mt-1"
            />
          </div>
        </BaseInputGroup>
      </BaseInputGrid>

      <!-- Preview -->
      <div
        v-if="calculatedAmount > 0"
        class="mt-6 p-4 bg-gray-50 rounded-md border border-gray-200"
      >
        <div class="flex justify-between items-center">
          <span class="text-sm font-medium text-gray-700">
            {{ $t('estimates.deposit_amount') }}
          </span>
          <span class="text-lg font-semibold text-gray-900">
            <BaseFormatMoney
              :amount="calculatedAmount"
              :currency="estimateCurrency"
            />
          </span>
        </div>
      </div>
    </div>

    <div
      class="z-0 flex justify-end p-4 border-t border-gray-200 border-solid"
    >
      <BaseButton
        class="mr-3 text-sm"
        type="button"
        variant="primary-outline"
        @click="closeModal"
      >
        {{ $t('general.cancel') }}
      </BaseButton>

      <BaseButton
        :loading="isLoading"
        :disabled="!isValid"
        variant="primary"
        type="button"
        @click="createDeposit"
      >
        <template #left="slotProps">
          <BaseIcon
            v-if="!isLoading"
            name="SaveIcon"
            :class="slotProps.class"
          />
        </template>
        {{ $t('estimates.create_deposit_invoice') }}
      </BaseButton>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed, ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useModalStore } from '@/scripts/stores/modal'
import { useEstimateStore } from '@/scripts/admin/stores/estimate'

const emit = defineEmits(['created'])

const modalStore = useModalStore()
const estimateStore = useEstimateStore()
const router = useRouter()
const { t } = useI18n()

const isLoading = ref(false)

const formData = reactive({
  deposit_type: 'percentage',
  deposit_value: 30,
})

const modalActive = computed(
  () => modalStore.active && modalStore.componentName === 'DepositInvoiceModal'
)

const estimateData = computed(() => modalStore.data || {})
const estimateId = computed(() => modalStore.id)

const estimateCurrency = computed(() => {
  return estimateData.value?.customer?.currency || null
})

const depositInvoices = computed(() => {
  return estimateData.value?.deposit_invoices || []
})

const existingDepositsTotal = computed(() => {
  return depositInvoices.value.reduce((sum, d) => sum + d.total, 0)
})

const remainingAmount = computed(() => {
  const total = estimateData.value?.total || 0
  return total - existingDepositsTotal.value
})

const calculatedAmount = computed(() => {
  const total = estimateData.value?.total || 0
  if (!formData.deposit_value || formData.deposit_value <= 0) return 0

  if (formData.deposit_type === 'percentage') {
    return Math.round(total * formData.deposit_value / 100)
  } else {
    return Math.round(formData.deposit_value)
  }
})

const inputError = computed(() => {
  if (calculatedAmount.value > remainingAmount.value && remainingAmount.value > 0) {
    return t('estimates.deposit_exceeds_total')
  }
  return null
})

const isValid = computed(() => {
  return (
    formData.deposit_value > 0 &&
    calculatedAmount.value > 0 &&
    calculatedAmount.value <= remainingAmount.value &&
    !isLoading.value
  )
})

async function createDeposit() {
  if (!isValid.value) return

  isLoading.value = true

  try {
    const response = await estimateStore.createDepositInvoice(estimateId.value, {
      deposit_type: formData.deposit_type,
      deposit_value: formData.deposit_value,
    })

    if (response.data) {
      closeModal()
      router.push(`/admin/invoices/${response.data.data.id}/edit`)
    }
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

function closeModal() {
  modalStore.closeModal()
  setTimeout(() => {
    formData.deposit_type = 'percentage'
    formData.deposit_value = 30
  }, 300)
}
</script>
