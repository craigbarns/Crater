<template>
  <div class="mt-6">
    <!-- Section Header -->
    <div class="flex items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">
        {{ $t('trade.trade_info') }}
      </h3>
    </div>

    <!-- Identification & Incoterm -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3 mb-4">
      <BaseInputGroup :label="$t('trade.contract_number')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].contract_number"
          :content-loading="isLoading"
          :placeholder="$t('trade.contract_number_placeholder')"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.incoterm')" :content-loading="isLoading">
        <BaseMultiselect
          v-model="store[storeProp].incoterm"
          :content-loading="isLoading"
          :options="incotermOptions"
          :can-clear="true"
          :can-deselect="true"
          :searchable="true"
          :create-option="true"
          class="w-full"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.country_of_origin')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].country_of_origin"
          :content-loading="isLoading"
        />
      </BaseInputGroup>
    </div>

    <!-- Commercial Conditions -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 mb-4">
      <BaseInputGroup :label="$t('trade.payment_terms')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].payment_terms"
          :content-loading="isLoading"
          :placeholder="$t('trade.payment_terms_placeholder')"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.delivery_lead_time')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].delivery_lead_time"
          :content-loading="isLoading"
          :placeholder="$t('trade.delivery_lead_time_placeholder')"
        />
      </BaseInputGroup>
    </div>

    <!-- Logistics -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3 mb-4">
      <BaseInputGroup :label="$t('trade.shipping_port')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].shipping_port"
          :content-loading="isLoading"
          :placeholder="$t('trade.shipping_port_placeholder')"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.destination_port')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].destination_port"
          :content-loading="isLoading"
          :placeholder="$t('trade.destination_port_placeholder')"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.transport_mode')" :content-loading="isLoading">
        <BaseMultiselect
          v-model="store[storeProp].transport_mode"
          :content-loading="isLoading"
          :options="transportModeOptions"
          :can-clear="true"
          :can-deselect="true"
          class="w-full"
        />
      </BaseInputGroup>
    </div>

    <!-- Weight, Packages, CBM -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-4">
      <BaseInputGroup :label="$t('trade.gross_weight_kg')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].gross_weight"
          :content-loading="isLoading"
          type="number"
          min="0"
          step="any"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.net_weight_kg')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].net_weight"
          :content-loading="isLoading"
          type="number"
          min="0"
          step="any"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.package_count')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].package_count"
          :content-loading="isLoading"
          type="number"
          min="0"
          step="1"
        />
      </BaseInputGroup>

      <BaseInputGroup :label="$t('trade.cbm')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].cbm"
          :content-loading="isLoading"
          type="number"
          min="0"
          step="any"
        />
      </BaseInputGroup>
    </div>

    <!-- B/L or AWB Number (invoice only) -->
    <div v-if="showBlAwb" class="grid grid-cols-1 gap-4 lg:grid-cols-2 mb-4">
      <BaseInputGroup :label="$t('trade.bl_awb_number')" :content-loading="isLoading">
        <BaseInput
          v-model="store[storeProp].bl_awb_number"
          :content-loading="isLoading"
          :placeholder="$t('trade.bl_awb_number_placeholder')"
        />
      </BaseInputGroup>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  store: {
    type: Object,
    required: true,
  },
  storeProp: {
    type: String,
    required: true,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  showBlAwb: {
    type: Boolean,
    default: false,
  },
})

const { t } = useI18n()

const incotermOptions = [
  'FOB Shanghai 2020',
  'FOB Shenzhen 2020',
  'FOB Ningbo 2020',
  'FOB Guangzhou 2020',
  'CIF Rotterdam 2020',
  'CIF Hamburg 2020',
  'CIF Le Havre 2020',
  'EXW Shenzhen 2020',
  'EXW Shanghai 2020',
  'DAP 2020',
  'DDP 2020',
  'CFR 2020',
  'CPT 2020',
]

const transportModeOptions = computed(() => [
  { label: t('trade.sea_freight'), value: 'sea' },
  { label: t('trade.air_freight'), value: 'air' },
  { label: t('trade.road_freight'), value: 'road' },
  { label: t('trade.rail_freight'), value: 'rail' },
  { label: t('trade.multimodal'), value: 'multimodal' },
])
</script>
