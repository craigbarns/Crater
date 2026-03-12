<?php

namespace Crater\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'supplier_id' => $this->supplier_id,
            'expense_id' => $this->expense_id,
            'payment_method_id' => $this->payment_method_id,
            'currency_id' => $this->currency_id,
            'company_id' => $this->company_id,
            'creator_id' => $this->creator_id,
            'exchange_rate' => $this->exchange_rate,
            'base_amount' => $this->base_amount,
            'formatted_created_at' => $this->formattedCreatedAt,
            'formatted_payment_date' => $this->formattedPaymentDate,
            'supplier' => $this->when($this->relationLoaded('supplier'), function () {
                return new SupplierResource($this->supplier);
            }),
            'expense' => $this->when($this->relationLoaded('expense'), function () {
                return new ExpenseResource($this->expense);
            }),
            'payment_method' => $this->when($this->relationLoaded('paymentMethod'), function () {
                return $this->paymentMethod;
            }),
            'currency' => $this->when($this->relationLoaded('currency'), function () {
                return $this->currency;
            }),
        ];
    }
}
