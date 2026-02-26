import Guid from 'guid'
import invoiceItemStub from './invoice-item'
import taxStub from './tax'

export default function () {
  return {
    id: null,
    invoice_number: '',
    customer: null,
    customer_id: null,
    template_name: null,
    invoice_date: '',
    due_date: '',
    notes: '',
    discount: 0,
    discount_type: 'fixed',
    discount_val: 0,
    reference_number: null,
    tax: 0,
    sub_total: 0,
    total: 0,
    tax_per_item: null,
    sales_tax_type: null,
    sales_tax_address_type: null,
    discount_per_item: null,
    taxes: [],
    items: [
      {
        ...invoiceItemStub,
        id: Guid.raw(),
        taxes: [{ ...taxStub, id: Guid.raw() }],
      },
    ],
    customFields: [],
    fields: [],
    selectedNote: null,
    selectedCurrency: '',
    currency_id: null,
    contract_number: null,
    incoterm: null,
    payment_terms: null,
    delivery_lead_time: null,
    shipping_port: null,
    destination_port: null,
    transport_mode: null,
    gross_weight: null,
    net_weight: null,
    package_count: null,
    cbm: null,
    bl_awb_number: null,
    country_of_origin: 'China',
  }
}
