<template>
  <div class="md:grid-cols-12 grid-cols-1 md:gap-x-6 mt-6 mb-8 grid gap-y-5">
    <BaseCustomerSelectPopup
      v-model="estimateStore.newEstimate.customer"
      :valid="v.customer_id"
      :content-loading="isLoading"
      type="estimate"
      class="col-span-5 pr-0"
    />

    <BaseInputGrid class="col-span-7">
      <BaseInputGroup
        :label="$t('reports.estimates.estimate_date')"
        :content-loading="isLoading"
        required
        :error="v.estimate_date.$error && v.estimate_date.$errors[0].$message"
      >
        <BaseDatePicker
          v-model="estimateStore.newEstimate.estimate_date"
          :content-loading="isLoading"
          :calendar-button="true"
          calendar-button-icon="calendar"
        />
      </BaseInputGroup>

      <BaseInputGroup
        :label="$t('estimates.expiry_date')"
        :content-loading="isLoading"
      >
        <BaseDatePicker
          v-model="estimateStore.newEstimate.expiry_date"
          :content-loading="isLoading"
          :calendar-button="true"
          calendar-button-icon="calendar"
        />
      </BaseInputGroup>

      <BaseInputGroup
        :label="$t('estimates.estimate_number')"
        :content-loading="isLoading"
        required
        :error="
          v.estimate_number.$error && v.estimate_number.$errors[0].$message
        "
      >
        <BaseInput
          v-model="estimateStore.newEstimate.estimate_number"
          :content-loading="isLoading"
        >
        </BaseInput>
      </BaseInputGroup>

      <!-- <BaseInputGroup
        :label="$t('estimates.ref_number')"
        :content-loading="isLoading"
        :error="
          v.reference_number.$error && v.reference_number.$errors[0].$message
        "
      >
        <BaseInput
          v-model="estimateStore.newEstimate.reference_number"
          :content-loading="isLoading"
          @input="v.reference_number.$touch()"
        >
          <template #left="slotProps">
            <BaseIcon name="HashtagIcon" :class="slotProps.class" />
          </template>
        </BaseInput>
      </BaseInputGroup> -->
      <BaseInputGroup
        :label="$t('settings.preferences.currency')"
        :content-loading="isLoading"
      >
        <BaseMultiselect
          v-model="estimateStore.newEstimate.currency_id"
          :content-loading="isLoading"
          :options="globalStore.currencies"
          label="name"
          value-prop="id"
          :searchable="true"
          track-by="name"
          class="w-full"
        />
      </BaseInputGroup>

      <ExchangeRateConverter
        :store="estimateStore"
        store-prop="newEstimate"
        :v="v"
        :is-loading="isLoading"
        :is-edit="isEdit"
        :customer-currency="estimateStore.newEstimate.currency_id"
      />

      <BaseInputGroup
        :label="$t('trade.contract_number')"
        :content-loading="isLoading"
      >
        <BaseInput
          v-model="estimateStore.newEstimate.contract_number"
          :content-loading="isLoading"
          :placeholder="$t('trade.contract_number_placeholder')"
        />
      </BaseInputGroup>
    </BaseInputGrid>
  </div>
</template>

<script setup>
import { useEstimateStore } from '@/scripts/admin/stores/estimate'
import { useGlobalStore } from '@/scripts/admin/stores/global'
import ExchangeRateConverter from '@/scripts/admin/components/estimate-invoice-common/ExchangeRateConverter.vue'
import { watch } from 'vue'

const props = defineProps({
  v: {
    type: Object,
    default: null,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
})

const estimateStore = useEstimateStore()
const globalStore = useGlobalStore()

watch(
  () => estimateStore.newEstimate.currency_id,
  (id) => {
    const found = globalStore.currencies.find((c) => c.id === id)
    if (found) {
      estimateStore.newEstimate.selectedCurrency = found
    }
  }
)
</script>
