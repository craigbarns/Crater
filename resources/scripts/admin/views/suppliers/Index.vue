<template>
  <BasePage>
    <BasePageHeader :title="$t('suppliers.title')">
      <BaseBreadcrumb>
        <BaseBreadcrumbItem :title="$t('general.home')" to="dashboard" />
        <BaseBreadcrumbItem
          :title="$tc('suppliers.supplier', 2)"
          to="#"
          active
        />
      </BaseBreadcrumb>

      <template #actions>
        <div class="flex items-center justify-end space-x-5">
          <BaseButton
            v-show="supplierStore.totalSuppliers"
            variant="primary-outline"
            @click="toggleFilter"
          >
            {{ $t('general.filter') }}
            <template #right="slotProps">
              <BaseIcon
                v-if="!showFilters"
                name="FilterIcon"
                :class="slotProps.class"
              />
              <BaseIcon v-else name="XIcon" :class="slotProps.class" />
            </template>
          </BaseButton>

          <BaseButton
            v-if="userStore.hasAbilities(abilities.CREATE_SUPPLIER)"
            @click="$router.push('suppliers/create')"
          >
            <template #left="slotProps">
              <BaseIcon name="PlusIcon" :class="slotProps.class" />
            </template>
            {{ $t('suppliers.new_supplier') }}
          </BaseButton>
        </div>
      </template>
    </BasePageHeader>

    <BaseFilterWrapper :show="showFilters" class="mt-5" @clear="clearFilter">
      <BaseInputGroup :label="$t('suppliers.name')" class="text-left">
        <BaseInput
          v-model="filters.display_name"
          type="text"
          name="name"
          autocomplete="off"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('suppliers.contact_name')" class="text-left">
        <BaseInput
          v-model="filters.contact_name"
          type="text"
          name="contact_name"
          autocomplete="off"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('suppliers.phone')" class="text-left">
        <BaseInput
          v-model="filters.phone"
          type="text"
          name="phone"
          autocomplete="off"
        />
      </BaseInputGroup>
    </BaseFilterWrapper>

    <BaseEmptyPlaceholder
      v-show="showEmptyScreen"
      :title="$t('suppliers.no_suppliers')"
      :description="$t('suppliers.list_of_suppliers')"
    >
      <AstronautIcon class="mt-5 mb-4" />

      <template #actions>
        <BaseButton
          v-if="userStore.hasAbilities(abilities.CREATE_SUPPLIER)"
          variant="primary-outline"
          @click="$router.push('/admin/suppliers/create')"
        >
          <template #left="slotProps">
            <BaseIcon name="PlusIcon" :class="slotProps.class" />
          </template>
          {{ $t('suppliers.add_new_supplier') }}
        </BaseButton>
      </template>
    </BaseEmptyPlaceholder>

    <div v-show="!showEmptyScreen" class="relative table-container">
      <div class="relative flex items-center justify-end h-5">
        <BaseDropdown v-if="supplierStore.selectedSuppliers.length">
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
          <BaseDropdownItem @click="removeMultipleSuppliers">
            <BaseIcon name="TrashIcon" class="mr-3 text-gray-600" />
            {{ $t('general.delete') }}
          </BaseDropdownItem>
        </BaseDropdown>
      </div>

      <BaseTable
        ref="tableComponent"
        class="mt-3"
        :data="fetchData"
        :columns="supplierColumns"
      >
        <template #header>
          <div class="absolute z-10 items-center left-6 top-2.5 select-none">
            <BaseCheckbox
              v-model="selectAllFieldStatus"
              variant="primary"
              @change="supplierStore.selectAllSuppliers"
            />
          </div>
        </template>

        <template #cell-status="{ row }">
          <div class="relative block">
            <BaseCheckbox
              :id="row.data.id"
              v-model="selectField"
              :value="row.data.id"
              variant="primary"
            />
          </div>
        </template>

        <template #cell-name="{ row }">
          <router-link :to="{ path: `suppliers/${row.data.id}/edit` }">
            <BaseText
              :text="row.data.name"
              :length="30"
              tag="span"
              class="font-medium text-primary-500 flex flex-col"
            />
            <BaseText
              :text="row.data.contact_name ? row.data.contact_name : ''"
              :length="30"
              tag="span"
              class="text-xs text-gray-400"
            />
          </router-link>
        </template>

        <template #cell-phone="{ row }">
          <span>
            {{ row.data.phone ? row.data.phone : '-' }}
          </span>
        </template>

        <template #cell-email="{ row }">
          <span>
            {{ row.data.email ? row.data.email : '-' }}
          </span>
        </template>

        <template #cell-created_at="{ row }">
          <span>{{ row.data.formatted_created_at }}</span>
        </template>

        <template v-if="hasAtleastOneAbility()" #cell-actions="{ row }">
          <SupplierDropdown
            :row="row.data"
            :table="tableComponent"
            :load-data="refreshTable"
          />
        </template>
      </BaseTable>
    </div>
  </BasePage>
</template>

<script setup>
import { debouncedWatch } from '@vueuse/core'
import { reactive, ref, computed, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useSupplierStore } from '@/scripts/admin/stores/supplier'
import { useDialogStore } from '@/scripts/stores/dialog'
import { useUserStore } from '@/scripts/admin/stores/user'

import abilities from '@/scripts/admin/stub/abilities'

import SupplierDropdown from '@/scripts/admin/components/dropdowns/SupplierIndexDropdown.vue'
import AstronautIcon from '@/scripts/components/icons/empty/AstronautIcon.vue'

const dialogStore = useDialogStore()
const supplierStore = useSupplierStore()
const userStore = useUserStore()

let tableComponent = ref(null)
let showFilters = ref(false)
let isFetchingInitialData = ref(true)
const { t } = useI18n()

let filters = reactive({
  display_name: '',
  contact_name: '',
  phone: '',
})

const showEmptyScreen = computed(
  () => !supplierStore.totalSuppliers && !isFetchingInitialData.value
)

const selectField = computed({
  get: () => supplierStore.selectedSuppliers,
  set: (value) => {
    return supplierStore.selectSupplier(value)
  },
})

const selectAllFieldStatus = computed({
  get: () => supplierStore.selectAllField,
  set: (value) => {
    return supplierStore.setSelectAllState(value)
  },
})

const supplierColumns = computed(() => {
  return [
    {
      key: 'status',
      thClass: 'extra w-10 pr-0',
      sortable: false,
      tdClass: 'font-medium text-gray-900 pr-0',
    },
    {
      key: 'name',
      label: t('suppliers.name'),
      thClass: 'extra',
      tdClass: 'font-medium text-gray-900',
    },
    { key: 'phone', label: t('suppliers.phone') },
    { key: 'email', label: t('suppliers.email') },
    {
      key: 'created_at',
      label: t('items.added_on'),
    },
    {
      key: 'actions',
      tdClass: 'text-right text-sm font-medium pl-0',
      thClass: 'pl-0',
      sortable: false,
    },
  ]
})

debouncedWatch(
  filters,
  () => {
    setFilters()
  },
  { debounce: 500 }
)

onUnmounted(() => {
  if (supplierStore.selectAllField) {
    supplierStore.selectAllSuppliers()
  }
})

function refreshTable() {
  tableComponent.value.refresh()
}

function setFilters() {
  refreshTable()
}

function hasAtleastOneAbility() {
  return userStore.hasAbilities([
    abilities.DELETE_SUPPLIER,
    abilities.EDIT_SUPPLIER,
    abilities.VIEW_SUPPLIER,
  ])
}

async function fetchData({ page, filter, sort }) {
  let data = {
    display_name: filters.display_name,
    contact_name: filters.contact_name,
    phone: filters.phone,
    orderByField: sort.fieldName || 'created_at',
    orderBy: sort.order || 'desc',
    page,
  }

  isFetchingInitialData.value = true
  let response = await supplierStore.fetchSuppliers(data)
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

function clearFilter() {
  filters.display_name = ''
  filters.contact_name = ''
  filters.phone = ''
}

function toggleFilter() {
  if (showFilters.value) {
    clearFilter()
  }

  showFilters.value = !showFilters.value
}

function removeMultipleSuppliers() {
  dialogStore
    .openDialog({
      title: t('general.are_you_sure'),
      message: t('suppliers.confirm_delete', 2),
      yesLabel: t('general.ok'),
      noLabel: t('general.cancel'),
      variant: 'danger',
      hideNoButton: false,
      size: 'lg',
    })
    .then((res) => {
      if (res) {
        supplierStore.deleteMultipleSuppliers().then((response) => {
          if (response.data) {
            refreshTable()
          }
        })
      }
    })
}
</script>
