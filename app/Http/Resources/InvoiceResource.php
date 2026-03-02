<?php

namespace Crater\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'invoice_number' => $this->invoice_number,
            'reference_number' => $this->reference_number,
            'status' => $this->status,
            'paid_status' => $this->paid_status,
            'tax_per_item' => $this->tax_per_item,
            'discount_per_item' => $this->discount_per_item,
            'notes' => $this->notes,
            'discount_type' => $this->discount_type,
            'discount' => $this->discount,
            'discount_val' => $this->discount_val,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'tax' => $this->tax,
            'due_amount' => $this->due_amount,
            'sent' => $this->sent,
            'viewed' => $this->viewed,
            'unique_hash' => $this->unique_hash,
            'template_name' => $this->template_name,
            'customer_id' => $this->customer_id,
            'recurring_invoice_id' => $this->recurring_invoice_id,
            'sequence_number' => $this->sequence_number,
            'exchange_rate' => $this->exchange_rate,
            'base_discount_val' => $this->base_discount_val,
            'base_sub_total' => $this->base_sub_total,
            'base_total' => $this->base_total,
            'creator_id' => $this->creator_id,
            'base_tax' => $this->base_tax,
            'base_due_amount' => $this->base_due_amount,
            'currency_id' => $this->currency_id,
            'formatted_created_at' => $this->formattedCreatedAt,
            'invoice_pdf_url' => $this->invoicePdfUrl,
            'formatted_invoice_date' => $this->formattedInvoiceDate,
            'formatted_due_date' => $this->formattedDueDate,
            'allow_edit' => $this->allow_edit,
            'payment_module_enabled' => $this->payment_module_enabled,
            'sales_tax_type' => $this->sales_tax_type,
            'sales_tax_address_type' => $this->sales_tax_address_type,
            'overdue' => $this->overdue,
            'invoice_type' => $this->invoice_type,
            'estimate_id' => $this->estimate_id,
            'parent_invoice_id' => $this->parent_invoice_id,
            'deposit_percentage' => $this->deposit_percentage,
            'contract_number' => $this->contract_number,
            'incoterm' => $this->incoterm,
            'payment_terms' => $this->payment_terms,
            'delivery_lead_time' => $this->delivery_lead_time,
            'shipping_port' => $this->shipping_port,
            'destination_port' => $this->destination_port,
            'transport_mode' => $this->transport_mode,
            'gross_weight' => $this->gross_weight,
            'net_weight' => $this->net_weight,
            'package_count' => $this->package_count,
            'cbm' => $this->cbm,
            'bl_awb_number' => $this->bl_awb_number,
            'country_of_origin' => $this->country_of_origin,
            'items' => $this->when($this->items()->exists(), function () {
                return InvoiceItemResource::collection($this->items);
            }),
            'customer' => $this->when($this->customer()->exists(), function () {
                return new CustomerResource($this->customer);
            }),
            'creator' => $this->when($this->creator()->exists(), function () {
                return new UserResource($this->creator);
            }),
            'taxes' => $this->when($this->taxes()->exists(), function () {
                return TaxResource::collection($this->taxes);
            }),
            'fields' => $this->when($this->fields()->exists(), function () {
                return CustomFieldValueResource::collection($this->fields);
            }),
            'company' => $this->when($this->company()->exists(), function () {
                return new CompanyResource($this->company);
            }),
            'currency' => $this->when($this->currency()->exists(), function () {
                return new CurrencyResource($this->currency);
            }),
            'deposit_invoices' => $this->when($this->invoice_type === 'final', function () {
                return InvoiceResource::collection($this->depositInvoices);
            }),
            'estimate' => $this->when($this->estimate_id && $this->relationLoaded('estimate'), function () {
                return new EstimateResource($this->estimate);
            }),
        ];
    }
}
