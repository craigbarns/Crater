import axios from 'axios'
import moment from 'moment'
import { defineStore } from 'pinia'
import { useRoute } from 'vue-router'
import { useCompanyStore } from './company'
import { useNotificationStore } from '@/scripts/stores/notification'
import { useGlobalStore } from '@/scripts/admin/stores/global'
import supplierPaymentStub from '../stub/supplier-payment'
import { handleError } from '@/scripts/helpers/error-handling'

export const useSupplierPaymentStore = (useWindow = false) => {
  const defineStoreFunc = useWindow ? window.pinia.defineStore : defineStore
  const { global } = window.i18n

  return defineStoreFunc({
    id: 'supplierPayment',

    state: () => ({
      supplierPayments: [],
      supplierPaymentTotalCount: 0,

      selectAllField: false,
      selectedSupplierPayments: [],
      showExchangeRate: false,

      currentSupplierPayment: {
        ...supplierPaymentStub,
      },

      isFetchingInitialData: false,
    }),

    actions: {
      fetchSupplierPaymentInitialData(isEdit) {
        const companyStore = useCompanyStore()
        const route = useRoute()
        const globalStore = useGlobalStore()

        this.isFetchingInitialData = true

        let actions = []
        if (isEdit) {
          actions = [this.fetchSupplierPayment(route.params.id)]
        }

        Promise.all([
          this.fetchPaymentModes({ limit: 'all' }),
          this.getNextNumber(),
          ...actions,
        ])
          .then(async ([res1, res2, res3]) => {
            if (!isEdit && res2.data) {
              this.currentSupplierPayment.payment_date =
                moment().format('YYYY-MM-DD')
              this.currentSupplierPayment.payment_number =
                res2.data.nextNumber

              // Default to USD
              const usdCurrency = globalStore.currencies.find(
                (c) => c.code === 'USD'
              )
              this.currentSupplierPayment.currency =
                usdCurrency || companyStore.selectedCompanyCurrency
              this.currentSupplierPayment.currency_id =
                this.currentSupplierPayment.currency?.id || ''
            }

            this.isFetchingInitialData = false
          })
          .catch((err) => {
            handleError(err)
          })
      },

      fetchSupplierPayments(params) {
        return new Promise((resolve, reject) => {
          axios
            .get(`/api/v1/supplier-payments`, { params })
            .then((response) => {
              this.supplierPayments = response.data.data
              this.supplierPaymentTotalCount =
                response.data.meta.supplier_payment_total_count
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      fetchSupplierPayment(id) {
        return new Promise((resolve, reject) => {
          axios
            .get(`/api/v1/supplier-payments/${id}`)
            .then((response) => {
              Object.assign(
                this.currentSupplierPayment,
                response.data.data
              )
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      addSupplierPayment(data) {
        return new Promise((resolve, reject) => {
          axios
            .post('/api/v1/supplier-payments', data)
            .then((response) => {
              this.supplierPayments.push(response.data.data)
              const notificationStore = useNotificationStore()
              notificationStore.showNotification({
                type: 'success',
                message: global.t(
                  'supplier_payments.created_message'
                ),
              })
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      updateSupplierPayment(data) {
        return new Promise((resolve, reject) => {
          axios
            .put(`/api/v1/supplier-payments/${data.id}`, data)
            .then((response) => {
              if (response.data) {
                let pos = this.supplierPayments.findIndex(
                  (p) => p.id === response.data.data.id
                )
                this.supplierPayments[pos] = data
                const notificationStore = useNotificationStore()
                notificationStore.showNotification({
                  type: 'success',
                  message: global.t(
                    'supplier_payments.updated_message'
                  ),
                })
              }
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      deleteSupplierPayment(id) {
        const notificationStore = useNotificationStore()
        return new Promise((resolve, reject) => {
          axios
            .post(`/api/v1/supplier-payments/delete`, id)
            .then((response) => {
              let index = this.supplierPayments.findIndex(
                (p) => p.id === id
              )
              this.supplierPayments.splice(index, 1)
              notificationStore.showNotification({
                type: 'success',
                message: global.t(
                  'supplier_payments.deleted_message',
                  1
                ),
              })
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      deleteMultipleSupplierPayments() {
        const notificationStore = useNotificationStore()
        return new Promise((resolve, reject) => {
          axios
            .post(`/api/v1/supplier-payments/delete`, {
              ids: this.selectedSupplierPayments,
            })
            .then((response) => {
              this.selectedSupplierPayments.forEach((p) => {
                let index = this.supplierPayments.findIndex(
                  (_p) => _p.id === p.id
                )
                this.supplierPayments.splice(index, 1)
              })
              notificationStore.showNotification({
                type: 'success',
                message: global.tc(
                  'supplier_payments.deleted_message',
                  2
                ),
              })
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      setSelectAllState(data) {
        this.selectAllField = data
      },

      selectSupplierPayment(data) {
        this.selectedSupplierPayments = data
        if (
          this.selectedSupplierPayments.length ===
          this.supplierPayments.length
        ) {
          this.selectAllField = true
        } else {
          this.selectAllField = false
        }
      },

      selectAllSupplierPayments() {
        if (
          this.selectedSupplierPayments.length ===
          this.supplierPayments.length
        ) {
          this.selectedSupplierPayments = []
          this.selectAllField = false
        } else {
          let allIds = this.supplierPayments.map((p) => p.id)
          this.selectedSupplierPayments = allIds
          this.selectAllField = true
        }
      },

      resetCurrentSupplierPayment() {
        this.currentSupplierPayment = {
          ...supplierPaymentStub,
        }
      },

      fetchPaymentModes(params) {
        return new Promise((resolve, reject) => {
          axios
            .get(`/api/v1/payment-methods`, { params })
            .then((response) => {
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      getNextNumber(params, setState = false) {
        return new Promise((resolve, reject) => {
          axios
            .get(`/api/v1/next-number?key=supplier_payment`, {
              params,
            })
            .then((response) => {
              if (setState) {
                this.currentSupplierPayment.payment_number =
                  response.data.nextNumber
              }
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },
    },
  })()
}
