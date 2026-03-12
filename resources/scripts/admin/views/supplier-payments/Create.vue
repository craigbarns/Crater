<template>
  <BasePage class="relative supplier-payment-create">
    <form action="" @submit.prevent="submitSupplierPaymentData">
      <BasePageHeader :title="pageTitle" class="mb-5">
        <BaseBreadcrumb>
          <BaseBreadcrumbItem
            :title="$t('general.home')"
            to="/admin/dashboard"
          />
          <BaseBreadcrumbItem
            :title="$t('supplier_payments.supplier_payment')"
            to="/admin/supplier-payments"
          />
          <BaseBreadcrumbItem :title="pageTitle" to="#" active />
        </BaseBreadcrumb>

        <template #actions>
          <BaseButton
            :loading="isSaving"
            :disabled="isSaving"
            variant="primary"
            type="submit"
            class="hidden sm:flex"
          >
            <template #left="slotProps">
              <BaseIcon
                v-if="!isSaving"
                name="SaveIcon"
                :class="slotProps.class"
              />
            </template>
            {{
              isEdit
                ? $t('supplier_payments.update_supplier_payment')
                : $t('supplier_payments.save_supplier_payment')
            }}
          </BaseButton>
        </template>
      </BasePageHeader>

      <BaseCard>
        <BaseInputGrid>
          <BaseInputGroup
            :label="$t('supplier_payments.date')"
            :content-loading="isLoadingContent"
            required
            :error="
              v$.currentSupplierPayment.payment_date.$error &&
              v$.currentSupplierPayment.payment_date.$errors[0].$message
            "
          >
            <BaseDatePicker
              v-model="supplierPaymentStore.currentSupplierPayment.payment_date"
              :content-loading="isLoadingContent"
              :calendar-button="true"
              calendar-button-icon="calendar"
              :invalid="v$.currentSupplierPayment.payment_date.$error"
              @update:modelValue="
                v$.currentSupplierPayment.payment_date.$touch()
              "
            />
          </BaseInputGroup>

          <BaseInputGroup
            :label="$t('supplier_payments.payment_number')"
            :content-loading="isLoadingContent"
            required
          >
            <BaseInput
              v-model="
                supplierPaymentStore.currentSupplierPayment.payment_number
              "
              :content-loading="isLoadingContent"
            />
          </BaseInputGroup>

          <BaseInputGroup
            :label="$t('supplier_payments.supplier')"
            :error="
              v$.currentSupplierPayment.supplier_id.$error &&
              v$.currentSupplierPayment.supplier_id.$errors[0].$message
            "
            :content-loading="isLoadingContent"
            required
          >
            <BaseMultiselect
              v-model="
                supplierPaymentStore.currentSupplierPayment.supplier_id
              "
              :content-loading="isLoadingContent"
              :invalid="v$.currentSupplierPayment.supplier_id.$error"
              :placeholder="$t('suppliers.select_a_supplier')"
              value-prop="id"
              track-by="name"
              label="name"
              :filter-results="false"
              resolve-on-load
              :delay="500"
              searchable
              :options="searchSupplier"
              @update:modelValue="v$.currentSupplierPayment.supplier_id.$touch()"
            />
          </BaseInputGroup>

          <BaseInputGroup
            :label="$t('supplier_payments.amount')"
            :content-loading="isLoadingContent"
            :error="
              v$.currentSupplierPayment.amount.$error &&
              v$.currentSupplierPayment.amount.$errors[0].$message
            "
            required
          >
            <div class="relative w-full">
              <BaseMoney
                :key="supplierPaymentStore.currentSupplierPayment.currency"
                v-model="amount"
                :currency="
                  supplierPaymentStore.currentSupplierPayment.currency
                "
                :content-loading="isLoadingContent"
                :invalid="v$.currentSupplierPayment.amount.$error"
                @update:modelValue="
                  v$.currentSupplierPayment.amount.$touch()
                "
              />
            </div>
          </BaseInputGroup>

          <BaseInputGroup
            :content-loading="isLoadingContent"
            :label="$t('supplier_payments.payment_mode')"
          >
            <BaseMultiselect
              v-model="
                supplierPaymentStore.currentSupplierPayment.payment_method_id
              "
              :content-loading="isLoadingContent"
              label="name"
              value-prop="id"
              track-by="name"
              :options="paymentModes"
              :placeholder="$t('payments.select_payment_mode')"
              searchable
            />
          </BaseInputGroup>

          <BaseInputGroup
            :content-loading="isLoadingContent"
            :label="$t('supplier_payments.expense')"
          >
            <BaseMultiselect
              v-model="
                supplierPaymentStore.currentSupplierPayment.expense_id
              "
              :content-loading="isLoadingContent"
              value-prop="id"
              track-by="expense_category_name"
              label="expense_category_name"
              :options="expenseList"
              :loading="isLoadingExpenses"
              :placeholder="$t('supplier_payments.select_expense')"
              searchable
            />
          </BaseInputGroup>
        </BaseInputGrid>

        <!-- Notes -->
        <div class="relative mt-6">
          <label class="mb-4 text-sm font-medium text-gray-800">
            {{ $t('supplier_payments.notes') }}
          </label>

          <BaseTextarea
            v-model="supplierPaymentStore.currentSupplierPayment.notes"
            :content-loading="isLoadingContent"
            rows="4"
            class="mt-1"
          />
        </div>

        <BaseButton
          :loading="isSaving"
          :content-loading="isLoadingContent"
          variant="primary"
          type="submit"
          class="flex justify-center w-full mt-4 sm:hidden md:hidden"
        >
          <template #left="slotProps">
            <BaseIcon
              v-if="!isSaving"
              name="SaveIcon"
              :class="slotProps.class"
            />
          </template>
          {{
            isEdit
              ? $t('supplier_payments.update_supplier_payment')
              : $t('supplier_payments.save_supplier_payment')
          }}
        </BaseButton>
      </BaseCard>
    </form>
  </BasePage>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { required, helpers, decimal } from '@vuelidate/validators'
import useVuelidate from '@vuelidate/core'
import { useSupplierPaymentStore } from '@/scripts/admin/stores/supplier-payment'
import { useSupplierStore } from '@/scripts/admin/stores/supplier'
import { useCompanyStore } from '@/scripts/admin/stores/company'
import { useGlobalStore } from '@/scripts/admin/stores/global'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const supplierPaymentStore = useSupplierPaymentStore()
const supplierStore = useSupplierStore()
const companyStore = useCompanyStore()
const globalStore = useGlobalStore()

let isSaving = ref(false)
let isLoadingExpenses = ref(false)
let expenseList = ref([])
let paymentModes = ref([])

const amount = computed({
  get: () => supplierPaymentStore.currentSupplierPayment.amount / 100,
  set: (value) => {
    supplierPaymentStore.currentSupplierPayment.amount = Math.round(
      value * 100
    )
  },
})

const isLoadingContent = computed(
  () => supplierPaymentStore.isFetchingInitialData
)

const isEdit = computed(() => route.name === 'supplier-payments.edit')

const pageTitle = computed(() => {
  if (isEdit.value) {
    return t('supplier_payments.edit_supplier_payment')
  }
  return t('supplier_payments.new_supplier_payment')
})

const rules = computed(() => {
  return {
    currentSupplierPayment: {
      supplier_id: {
        required: helpers.withMessage(t('validation.required'), required),
      },
      payment_date: {
        required: helpers.withMessage(t('validation.required'), required),
      },
      amount: {
        required: helpers.withMessage(t('validation.required'), required),
      },
    },
  }
})

const v$ = useVuelidate(rules, supplierPaymentStore)

// Reset state
supplierPaymentStore.resetCurrentSupplierPayment()

// Fetch initial data
globalStore.fetchCurrencies()
supplierPaymentStore.fetchSupplierPaymentInitialData(isEdit.value)

// Fetch payment modes
supplierPaymentStore.fetchPaymentModes({ limit: 'all' }).then((res) => {
  paymentModes.value = res.data.data
})

// If edit, load expenses for the supplier
if (isEdit.value) {
  loadExpensesForSupplier()
}

async function searchSupplier(search) {
  let res = await supplierStore.fetchSuppliers({ search })
  return res.data.data
}

async function loadExpensesForSupplier() {
  if (supplierPaymentStore.currentSupplierPayment.supplier_id) {
    isLoadingExpenses.value = true
    try {
      let res = await axios.get('/api/v1/expenses', {
        params: {
          supplier_id:
            supplierPaymentStore.currentSupplierPayment.supplier_id,
          limit: 'all',
        },
      })
      expenseList.value = res.data.data.map((e) => ({
        id: e.id,
        expense_category_name: `${e.expense_category?.name || ''} - ${
          e.formatted_expense_date || ''
        }`,
      }))
    } catch (err) {
      console.error(err)
    }
    isLoadingExpenses.value = false
  }
}

onBeforeUnmount(() => {
  supplierPaymentStore.resetCurrentSupplierPayment()
})

async function submitSupplierPaymentData() {
  v$.value.$touch()

  if (v$.value.$invalid) {
    return false
  }

  isSaving.value = true

  let data = {
    ...supplierPaymentStore.currentSupplierPayment,
  }

  try {
    const action = isEdit.value
      ? supplierPaymentStore.updateSupplierPayment
      : supplierPaymentStore.addSupplierPayment

    await action(data)

    router.push('/admin/supplier-payments')
  } catch (err) {
    isSaving.value = false
  }
}
</script>
