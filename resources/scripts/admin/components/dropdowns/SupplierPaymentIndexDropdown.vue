<template>
  <BaseDropdown>
    <template #activator>
      <BaseIcon name="DotsHorizontalIcon" class="h-5 text-gray-500" />
    </template>

    <!-- edit  -->
    <router-link
      v-if="userStore.hasAbilities(abilities.EDIT_SUPPLIER_PAYMENT)"
      :to="`/admin/supplier-payments/${row.id}/edit`"
    >
      <BaseDropdownItem>
        <BaseIcon
          name="PencilIcon"
          class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
        />
        {{ $t('general.edit') }}
      </BaseDropdownItem>
    </router-link>

    <!-- delete  -->
    <BaseDropdownItem
      v-if="userStore.hasAbilities(abilities.DELETE_SUPPLIER_PAYMENT)"
      @click="removeSupplierPayment(row.id)"
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
import { useDialogStore } from '@/scripts/stores/dialog'
import { useI18n } from 'vue-i18n'
import { useSupplierPaymentStore } from '@/scripts/admin/stores/supplier-payment'
import { useRoute, useRouter } from 'vue-router'
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
})

const dialogStore = useDialogStore()
const { t } = useI18n()
const supplierPaymentStore = useSupplierPaymentStore()
const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

function removeSupplierPayment(id) {
  dialogStore
    .openDialog({
      title: t('general.are_you_sure'),
      message: t('supplier_payments.confirm_delete', 1),
      yesLabel: t('general.ok'),
      noLabel: t('general.cancel'),
      variant: 'danger',
      size: 'lg',
      hideNoButton: false,
    })
    .then(async (res) => {
      if (res) {
        await supplierPaymentStore.deleteSupplierPayment({ ids: [id] })
        props.table && props.table.refresh()
        return true
      }
    })
}
</script>
