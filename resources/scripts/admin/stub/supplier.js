import addressStub from '@/scripts/admin/stub/address.js'

export default function () {
  return {
    name: '',
    contact_name: '',
    email: '',
    phone: null,
    currency_id: null,
    website: null,
    company_name: '',
    billing: { ...addressStub },
    customFields: [],
    fields: [],
  }
}
