<template>
  <BasePage>
    <form @submit.prevent="submitSupplierData">
      <BasePageHeader :title="pageTitle">
        <BaseBreadcrumb>
          <BaseBreadcrumbItem :title="$t('general.home')" to="dashboard" />

          <BaseBreadcrumbItem
            :title="$tc('suppliers.supplier', 2)"
            to="/admin/suppliers"
          />

          <BaseBreadcrumb-item :title="pageTitle" to="#" active />
        </BaseBreadcrumb>

        <template #actions>
          <div class="flex items-center justify-end">
            <BaseButton type="submit" :loading="isSaving" :disabled="isSaving">
              <template #left="slotProps">
                <BaseIcon name="SaveIcon" :class="slotProps.class" />
              </template>
              {{
                isEdit
                  ? $t('suppliers.update_supplier')
                  : $t('suppliers.save_supplier')
              }}
            </BaseButton>
          </div>
        </template>
      </BasePageHeader>

      <BaseCard class="mt-5">
        <!-- Basic Info -->
        <div class="grid grid-cols-5 gap-4 mb-8">
          <h6 class="col-span-5 text-lg font-semibold text-left lg:col-span-1">
            {{ $t('suppliers.basic_info') }}
          </h6>

          <BaseInputGrid class="col-span-5 lg:col-span-4">
            <BaseInputGroup
              :label="$t('suppliers.name')"
              required
              :error="
                v$.currentSupplier.name.$error &&
                v$.currentSupplier.name.$errors[0].$message
              "
              :content-loading="isFetchingInitialData"
            >
              <BaseInput
                v-model="supplierStore.currentSupplier.name"
                :content-loading="isFetchingInitialData"
                type="text"
                name="name"
                :invalid="v$.currentSupplier.name.$error"
                @input="v$.currentSupplier.name.$touch()"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :label="$t('suppliers.contact_name')"
              :content-loading="isFetchingInitialData"
            >
              <BaseInput
                v-model.trim="supplierStore.currentSupplier.contact_name"
                :content-loading="isFetchingInitialData"
                type="text"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :error="
                v$.currentSupplier.email.$error &&
                v$.currentSupplier.email.$errors[0].$message
              "
              :content-loading="isFetchingInitialData"
              :label="$t('suppliers.email')"
            >
              <BaseInput
                v-model.trim="supplierStore.currentSupplier.email"
                :content-loading="isFetchingInitialData"
                type="text"
                name="email"
                :invalid="v$.currentSupplier.email.$error"
                @input="v$.currentSupplier.email.$touch()"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :label="$t('suppliers.phone')"
              :content-loading="isFetchingInitialData"
            >
              <BaseInput
                v-model.trim="supplierStore.currentSupplier.phone"
                :content-loading="isFetchingInitialData"
                type="text"
                name="phone"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :label="$t('suppliers.company_name')"
              :content-loading="isFetchingInitialData"
            >
              <BaseInput
                v-model.trim="supplierStore.currentSupplier.company_name"
                :content-loading="isFetchingInitialData"
                type="text"
                name="company_name"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :label="$t('suppliers.primary_currency')"
              :content-loading="isFetchingInitialData"
              :error="
                v$.currentSupplier.currency_id.$error &&
                v$.currentSupplier.currency_id.$errors[0].$message
              "
              required
            >
              <BaseMultiselect
                v-model="supplierStore.currentSupplier.currency_id"
                value-prop="id"
                label="name"
                track-by="name"
                :content-loading="isFetchingInitialData"
                :options="globalStore.currencies"
                searchable
                :can-deselect="false"
                :placeholder="$t('suppliers.select_currency')"
                :invalid="v$.currentSupplier.currency_id.$error"
                class="w-full"
              >
              </BaseMultiselect>
            </BaseInputGroup>

            <BaseInputGroup
              :error="
                v$.currentSupplier.website.$error &&
                v$.currentSupplier.website.$errors[0].$message
              "
              :label="$t('suppliers.website')"
              :content-loading="isFetchingInitialData"
            >
              <BaseInput
                v-model="supplierStore.currentSupplier.website"
                :content-loading="isFetchingInitialData"
                type="url"
                @input="v$.currentSupplier.website.$touch()"
              />
            </BaseInputGroup>
          </BaseInputGrid>
        </div>

        <BaseDivider class="mb-5 md:mb-8" />

        <!-- Billing Address -->
        <div class="grid grid-cols-5 gap-4 mb-8">
          <h6 class="col-span-5 text-lg font-semibold text-left lg:col-span-1">
            {{ $t('suppliers.billing_address') }}
          </h6>

          <BaseInputGrid
            v-if="supplierStore.currentSupplier.billing"
            class="col-span-5 lg:col-span-4"
          >
            <BaseInputGroup
              :label="$t('suppliers.name')"
              :content-loading="isFetchingInitialData"
            >
              <BaseInput
                v-model.trim="supplierStore.currentSupplier.billing.name"
                :content-loading="isFetchingInitialData"
                type="text"
                name="address_name"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :label="$t('customers.country')"
              :content-loading="isFetchingInitialData"
            >
              <BaseMultiselect
                v-model="supplierStore.currentSupplier.billing.country_id"
                value-prop="id"
                label="name"
                track-by="name"
                resolve-on-load
                searchable
                :content-loading="isFetchingInitialData"
                :options="globalStore.countries"
                :placeholder="$t('general.select_country')"
                class="w-full"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :label="$t('customers.state')"
              :content-loading="isFetchingInitialData"
            >
              <BaseInput
                v-model="supplierStore.currentSupplier.billing.state"
                :content-loading="isFetchingInitialData"
                name="billing.state"
                type="text"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :content-loading="isFetchingInitialData"
              :label="$t('customers.city')"
            >
              <BaseInput
                v-model="supplierStore.currentSupplier.billing.city"
                :content-loading="isFetchingInitialData"
                name="billing.city"
                type="text"
              />
            </BaseInputGroup>

            <BaseInputGroup
              :label="$t('customers.address')"
              :error="
                (v$.currentSupplier.billing.address_street_1.$error &&
                  v$.currentSupplier.billing.address_street_1.$errors[0]
                    .$message) ||
                (v$.currentSupplier.billing.address_street_2.$error &&
                  v$.currentSupplier.billing.address_street_2.$errors[0]
                    .$message)
              "
              :content-loading="isFetchingInitialData"
            >
              <BaseTextarea
                v-model.trim="
                  supplierStore.currentSupplier.billing.address_street_1
                "
                :content-loading="isFetchingInitialData"
                :placeholder="$t('general.street_1')"
                type="text"
                name="billing_street1"
                :container-class="`mt-3`"
                @input="v$.currentSupplier.billing.address_street_1.$touch()"
              />

              <BaseTextarea
                v-model.trim="
                  supplierStore.currentSupplier.billing.address_street_2
                "
                :content-loading="isFetchingInitialData"
                :placeholder="$t('general.street_2')"
                type="text"
                class="mt-3"
                name="billing_street2"
                :container-class="`mt-3`"
                @input="v$.currentSupplier.billing.address_street_2.$touch()"
              />
            </BaseInputGroup>

            <div class="space-y-6">
              <BaseInputGroup
                :content-loading="isFetchingInitialData"
                :label="$t('customers.phone')"
                class="text-left"
              >
                <BaseInput
                  v-model.trim="supplierStore.currentSupplier.billing.phone"
                  :content-loading="isFetchingInitialData"
                  type="text"
                  name="phone"
                />
              </BaseInputGroup>

              <BaseInputGroup
                :label="$t('customers.zip_code')"
                :content-loading="isFetchingInitialData"
                class="mt-2 text-left"
              >
                <BaseInput
                  v-model.trim="supplierStore.currentSupplier.billing.zip"
                  :content-loading="isFetchingInitialData"
                  type="text"
                  name="zip"
                />
              </BaseInputGroup>
            </div>
          </BaseInputGrid>
        </div>

        <BaseDivider
          v-if="customFieldStore.customFields.length > 0"
          class="mb-5 md:mb-8"
        />

        <!-- Supplier Custom Fields -->
        <div class="grid grid-cols-5 gap-2 mb-8">
          <h6
            v-if="customFieldStore.customFields.length > 0"
            class="col-span-5 text-lg font-semibold text-left lg:col-span-1"
          >
            {{ $t('settings.custom_fields.title') }}
          </h6>

          <div class="col-span-5 lg:col-span-4">
            <SupplierCustomFields
              type="Supplier"
              :store="supplierStore"
              store-prop="currentSupplier"
              :is-edit="isEdit"
              :is-loading="isLoadingContent"
              :custom-field-scope="customFieldValidationScope"
            />
          </div>
        </div>
      </BaseCard>
    </form>
  </BasePage>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  required,
  minLength,
  url,
  maxLength,
  helpers,
  email,
} from '@vuelidate/validators'
import useVuelidate from '@vuelidate/core'
import { useSupplierStore } from '@/scripts/admin/stores/supplier'
import { useCustomFieldStore } from '@/scripts/admin/stores/custom-field'
import SupplierCustomFields from '@/scripts/admin/components/custom-fields/CreateCustomFields.vue'
import { useGlobalStore } from '@/scripts/admin/stores/global'

const supplierStore = useSupplierStore()
const customFieldStore = useCustomFieldStore()
const globalStore = useGlobalStore()

const customFieldValidationScope = 'customFields'

const { t } = useI18n()

const router = useRouter()
const route = useRoute()

let isFetchingInitialData = ref(false)
const isSaving = ref(false)

const isEdit = computed(() => route.name === 'suppliers.edit')

let isLoadingContent = computed(() => supplierStore.isFetchingInitialSettings)

const pageTitle = computed(() =>
  isEdit.value ? t('suppliers.edit_supplier') : t('suppliers.new_supplier')
)

const rules = computed(() => {
  return {
    currentSupplier: {
      name: {
        required: helpers.withMessage(t('validation.required'), required),
        minLength: helpers.withMessage(
          t('validation.name_min_length', { count: 3 }),
          minLength(3)
        ),
      },
      currency_id: {
        required: helpers.withMessage(t('validation.required'), required),
      },
      email: {
        email: helpers.withMessage(t('validation.email_incorrect'), email),
      },
      website: {
        url: helpers.withMessage(t('validation.invalid_url'), url),
      },
      billing: {
        address_street_1: {
          maxLength: helpers.withMessage(
            t('validation.address_maxlength', { count: 255 }),
            maxLength(255)
          ),
        },
        address_street_2: {
          maxLength: helpers.withMessage(
            t('validation.address_maxlength', { count: 255 }),
            maxLength(255)
          ),
        },
      },
    },
  }
})

const v$ = useVuelidate(rules, supplierStore, {
  $scope: customFieldValidationScope,
})

supplierStore.resetCurrentSupplier()

supplierStore.fetchSupplierInitialSettings(isEdit.value)

async function submitSupplierData() {
  v$.value.$touch()

  if (v$.value.$invalid) {
    return true
  }

  isSaving.value = true

  let data = {
    ...supplierStore.currentSupplier,
  }

  let response = null

  try {
    const action = isEdit.value
      ? supplierStore.updateSupplier
      : supplierStore.addSupplier
    response = await action(data)
  } catch (err) {
    isSaving.value = false
    return
  }

  router.push(`/admin/suppliers`)
}
</script>
