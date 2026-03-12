<?php

namespace Crater\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'payment_date' => ['required'],
            'supplier_id' => ['required'],
            'amount' => ['required'],
            'payment_number' => [
                'required',
                Rule::unique('supplier_payments')->where('company_id', $this->header('company')),
            ],
            'expense_id' => ['nullable'],
            'payment_method_id' => ['nullable'],
            'notes' => ['nullable'],
            'currency_id' => ['nullable'],
            'exchange_rate' => ['nullable'],
        ];

        if ($this->isMethod('PUT')) {
            $rules['payment_number'] = [
                'required',
                Rule::unique('supplier_payments')
                    ->where('company_id', $this->header('company'))
                    ->ignore($this->route('supplier_payment')->id),
            ];
        }

        return $rules;
    }

    public function getSupplierPaymentPayload()
    {
        $currency_id = $this->currency_id;

        if (!$currency_id && $this->supplier_id) {
            $supplier = \Crater\Models\Supplier::find($this->supplier_id);
            if ($supplier) {
                $currency_id = $supplier->currency_id;
            }
        }

        $exchangeRate = $this->exchange_rate ?? 1;

        return collect($this->validated())
            ->only([
                'payment_date',
                'payment_number',
                'amount',
                'notes',
                'supplier_id',
                'expense_id',
                'payment_method_id',
            ])
            ->merge([
                'creator_id' => $this->user()->id,
                'company_id' => $this->header('company'),
                'currency_id' => $currency_id,
                'exchange_rate' => $exchangeRate,
                'base_amount' => $this->amount * $exchangeRate,
            ])
            ->toArray();
    }
}
