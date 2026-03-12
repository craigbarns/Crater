<template>
  <BasePage class="supplier-payments">
    <BasePageHeader :title="$t('supplier_payments.title')">
      <BaseBreadcrumb>
        <BaseBreadcrumbItem :title="$t('general.home')" to="dashboard" />
        <BaseBreadcrumbItem
          :title="$t('supplier_payments.supplier_payment')"
          to="#"
          active
        />
      </BaseBreadcrumb>

      <template #actions>
        <BaseButton
          v-show="supplierPaymentStore.supplierPaymentTotalCount"
          variant="primary-outline"
          @click="toggleFilter"
        >
          {{ $t('general.filter') }}
          <template #right="slotProps">
            <BaseIcon
              v-if="!showFilters"
              :class="slotProps.class"
              name="FilterIcon"
            />
            <BaseIcon v-else name="XIcon" :class="slotProps.class" />
          </template>
        </BaseButton>

        <BaseButton
          v-if="userStore.hasAbilities(abilities.CREATE_SUPPLIER_PAYMENT)"
          variant="primary"
          class="ml-4"
          @click="$router.push('/admin/supplier-payments/create')"
        >
          <template #left="slotProps">
            <BaseIcon name="PlusIcon" :class="slotProps.class" />
          </template>
          {{ $t('supplier_payments.add_supplier_payment') }}
        </BaseButton>
      </template>
    </BasePageHeader>

    <BaseFilterWrapper :show="showFilters" class="mt-3" @clear="clearFilter">
      <BaseInputGroup :label="$t('supplier_payments.supplier')">
        <BaseMultiselect
          v-model="filters.supplier_id"
          value-prop="id"
          track-by="name"
          :filter-results="false"
          label="name"
          resolve-on-load
          :delay="500"
          searchable
          :options="searchSupplier"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('supplier_payments.payment_number')">
        <BaseInput v-model="filters.payment_number">
          <template #left="slotProps">
            <BaseIcon name="HashtagIcon" :class="slotProps.class" />
          </template>
        </BaseInput>
      </BaseInputGroup>

      <BaseInputGroup :label="$t('supplier_payments.payment_mode')">
        <BaseMultiselect
          v-model="filters.payment_method_id"
          value-prop="id"
          track-by="name"
          :filter-results="false"
          label="name"
          resolve-on-load
          :delay="500"
          searchable
          :options="searchPaymentMode"
        />
      </BaseInputGroup>
    </BaseFilterWrapper>

    <BaseEmptyPlaceholder
      v-if="showEmptyScreen"
      :title="$t('supplier_payments.no_supplier_payments')"
      :description="$t('supplier_payments.list_of_supplier_payments')"
    >
      <CapsuleIcon class="mt-5 mb-4" />

      <template
        v-if="userStore.hasAbilities(abilities.CREATE_SUPPLIER_PAYMENT)"
        #actions
      >
        <BaseButton
          variant="primary-outline"
          @click="$router.push('/admin/supplier-payments/create')"
        >
          <template #left="slotProps">
            <BaseIcon name="PlusIcon" :class="slotProps.class" />
          </template>
          {{ $t('supplier_payments.add_new_supplier_payment') }}
        </BaseButton>
      </template>
    </BaseEmptyPlaceholder>

    <div v-show="!showEmptyScreen" class="relative table-container">
      <!-- Multiple Select Actions -->
      <div class="relative flex items-center justify-end h-5">
        <BaseDropdown
          v-if="supplierPaymentStore.selectedSupplierPayments.length"
        >
          <template #activator>
            <span
              class="
                flex
                text-sm
                font-medium
                cursor-pointer
                select-none
                text-primary-400
              "
            >
              {{ $t('general.actions') }}
              <BaseIcon name="ChevronDownIcon" />
            </span>
          </template>
          <BaseDropdownItem @click="removeMultipleSupplierPayments">
            <BaseIcon name="TrashIcon" class="mr-3 text-gray-600" />
            {{ $t('general.delete') }}
          </BaseDropdownItem>
        </BaseDropdown>
      </div>

      <BaseTable
        ref="tableComponent"
        :data="fetchData"
        :columns="supplierPaymentColumns"
        :placeholder-count="
          supplierPaymentStore.supplierPaymentTotalCount >= 20 ? 10 : 5
        "
        class="mt-3"
      >
        <!-- Select All Checkbox -->
        <template #header>
          <div class="absolute items-center left-6 top-2.5 select-none">
            <BaseCheckbox
              v-model="selectAllFieldStatus"
              variant="primary"
              @change="supplierPaymentStore.selectAllSupplierPayments"
            />
          </div>
        </template>

        <template #cell-status="{ row }">
          <div class="relative block">
            <BaseCheckbox
              :id="row.id"
              v-model="selectField"
              :value="row.data.id"
              variant="primary"
            />
          </div>
        </template>

        <template #cell-payment_date="{ row }">
          {{ row.data.formatted_payment_date }}
        </template>

        <template #cell-payment_number="{ row }">
          <span class="font-medium text-primary-500">
            {{ row.data.payment_number }}
          </span>
        </template>

        <template #cell-supplier_name="{ row }">
          <BaseText
            :text="row.data.supplier ? row.data.supplier.name : '-'"
            :length="30"
            tag="span"
          />
        </template>

        <template #cell-payment_mode="{ row }">
          <span>
            {{ row.data.payment_method ? row.data.payment_method.name : '-' }}
          </span>
        </template>

        <template #cell-amount="{ row }">
          <BaseFormatMoney
            :amount="row.data.amount"
            :currency="row.data.currency"
          />
        </template>

        <template v-if="hasAtleastOneAbility()" #cell-actions="{ row }">
          <SupplierPaymentDropdown
            :row="row.data"
            :table="tableComponent"
          />
        </template>
      </BaseTable>
    </div>
  </BasePage>
</template>

<script setup>
import { debouncedWatch } from '@vueuse/core'
import { ref, reactive, computed, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDialogStore } from '@/scripts/stores/dialog'
import { useSupplierPaymentStore } from '@/scripts/admin/stores/supplier-payment'
import { useSupplierStore } from '@/scripts/admin/stores/supplier'
import { usePaymentStore } from '@/scripts/admin/stores/payment'
import { useUserStore } from '@/scripts/admin/stores/user'
import abilities from '@/scripts/admin/stub/abilities'
import CapsuleIcon from '@/scripts/components/icons/empty/CapsuleIcon.vue'
import SupplierPaymentDropdown from '@/scripts/admin/components/dropdowns/SupplierPaymentIndexDropdown.vue'

const { t } = useI18n()
let showFilters = ref(false)
let isFetchingInitialData = ref(true)
let tableComponent = ref(null)

const filters = reactive({
  supplier_id: '',
  payment_method_id: '',
  payment_number: '',
})

const supplierPaymentStore = useSupplierPaymentStore()
const supplierStore = useSupplierStore()
const paymentStore = usePaymentStore()
const dialogStore = useDialogStore()
const userStore = useUserStore()

const showEmptyScreen = computed(() => {
  return (
    !supplierPaymentStore.supplierPaymentTotalCount &&
    !isFetchingInitialData.value
  )
})

const supplierPaymentColumns = computed(() => {
  return [
    {
      key: 'status',
      sortable: false,
      thClass: 'extra w-10',
      tdClass: 'text-left text-sm font-medium extra',
    },
    {
      key: 'payment_date',
      label: t('supplier_payments.date'),
      thClass: 'extra',
      tdClass: 'font-medium text-gray-900',
    },
    { key: 'payment_number', label: t('supplier_payments.payment_number') },
    { key: 'supplier_name', label: t('supplier_payments.supplier') },
    { key: 'payment_mode', label: t('supplier_payments.payment_mode') },
    { key: 'amount', label: t('supplier_payments.amount') },
    {
      key: 'actions',
      label: '',
      tdClass: 'text-right text-sm font-medium',
      sortable: false,
    },
  ]
})

const selectField = computed({
  get: () => supplierPaymentStore.selectedSupplierPayments,
  set: (value) => {
    return supplierPaymentStore.selectSupplierPayment(value)
  },
})

const selectAllFieldStatus = computed({
  get: () => supplierPaymentStore.selectAllField,
  set: (value) => {
    return supplierPaymentStore.setSelectAllState(value)
  },
})

debouncedWatch(
  filters,
  () => {
    setFilters()
  },
  { debounce: 500 }
)

onUnmounted(() => {
  if (supplierPaymentStore.selectAllField) {
    supplierPaymentStore.selectAllSupplierPayments()
  }
})

async function searchSupplier(search) {
  let res = await supplierStore.fetchSuppliers({ search })
  return res.data.data
}

async function searchPaymentMode(search) {
  let res = await paymentStore.fetchPaymentModes({ search })
  return res.data.data
}

function hasAtleastOneAbility() {
  return userStore.hasAbilities([
    abilities.DELETE_SUPPLIER_PAYMENT,
    abilities.EDIT_SUPPLIER_PAYMENT,
  ])
}

async function fetchData({ page, filter, sort }) {
  let data = {
    supplier_id: filters.supplier_id,
    payment_method_id:
      filters.payment_method_id !== null ? filters.payment_method_id : '',
    payment_number: filters.payment_number,
    orderByField: sort.fieldName || 'created_at',
    orderBy: sort.order || 'desc',
    page,
  }

  isFetchingInitialData.value = true

  let response = await supplierPaymentStore.fetchSupplierPayments(data)

  isFetchingInitialData.value = false

  return {
    data: response.data.data,
    pagination: {
      totalPages: response.data.meta.last_page,
      currentPage: page,
      totalCount: response.data.meta.total,
      limit: 10,
    },
  }
}

function refreshTable() {
  tableComponent.value && tableComponent.value.refresh()
}

function setFilters() {
  refreshTable()
}

function clearFilter() {
  filters.supplier_id = ''
  filters.payment_method_id = ''
  filters.payment_number = ''
}

function toggleFilter() {
  if (showFilters.value) {
    clearFilter()
  }
  showFilters.value = !showFilters.value
}

function removeMultipleSupplierPayments() {
  dialogStore
    .openDialog({
      title: t('general.are_you_sure'),
      message: t('supplier_payments.confirm_delete', 2),
      yesLabel: t('general.ok'),
      noLabel: t('general.cancel'),
      variant: 'danger',
      hideNoButton: false,
      size: 'lg',
    })
    .then((res) => {
      if (res) {
        supplierPaymentStore
          .deleteMultipleSupplierPayments()
          .then((response) => {
            if (response.data.success) {
              refreshTable()
            }
          })
      }
    })
}
</script>
