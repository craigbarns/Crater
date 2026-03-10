<template>
  <BaseDropdown>
    <template #activator>
      <BaseIcon name="DotsHorizontalIcon" class="h-5 text-gray-500" />
    </template>

    <!-- Edit Supplier -->
    <router-link
      v-if="userStore.hasAbilities(abilities.EDIT_SUPPLIER)"
      :to="`/admin/suppliers/${row.id}/edit`"
    >
      <BaseDropdownItem>
        <BaseIcon
          name="PencilIcon"
          class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
        />
        {{ $t('general.edit') }}
      </BaseDropdownItem>
    </router-link>

    <!-- Delete Supplier -->
    <BaseDropdownItem
      v-if="userStore.hasAbilities(abilities.DELETE_SUPPLIER)"
      @click="removeSupplier(row.id)"
    >
      <BaseIcon
        name="TrashIcon"
        class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
      />
      {{ $t('general.delete') }}
    </BaseDropdownItem>
  </BaseDropdown>
</template>

<script setup>
import { useSupplierStore } from '@/scripts/admin/stores/supplier'
import { useDialogStore } from '@/scripts/stores/dialog'
import { useI18n } from 'vue-i18n'
import { useUserStore } from '@/scripts/admin/stores/user'
import abilities from '@/scripts/admin/stub/abilities'

const props = defineProps({
  row: {
    type: Object,
    default: null,
  },
  table: {
    type: Object,
    default: null,
  },
  loadData: {
    type: Function,
    default: () => {},
  },
})

const supplierStore = useSupplierStore()
const dialogStore = useDialogStore()
const userStore = useUserStore()

const { t } = useI18n()

function removeSupplier(id) {
  dialogStore
    .openDialog({
      title: t('general.are_you_sure'),
      message: t('suppliers.confirm_delete', 1),
      yesLabel: t('general.ok'),
      noLabel: t('general.cancel'),
      variant: 'danger',
      hideNoButton: false,
      size: 'lg',
    })
    .then((res) => {
      if (res) {
        supplierStore.deleteSupplier({ ids: [id] }).then((response) => {
          if (response.data.success) {
            props.loadData && props.loadData()
            return true
          }
        })
      }
    })
}
</script>
