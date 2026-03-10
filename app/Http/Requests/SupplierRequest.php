<?php

namespace Crater\Http\Requests;

use Crater\Models\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => [
                'required',
            ],
            'email' => [
                'email',
                'nullable',
                Rule::unique('suppliers')->where('company_id', $this->header('company'))
            ],
            'phone' => [
                'nullable',
            ],
            'company_name' => [
                'nullable',
            ],
            'contact_name' => [
                'nullable',
            ],
            'website' => [
                'nullable',
            ],
            'currency_id' => [
                'nullable',
            ],
            'billing.name' => [
                'nullable',
            ],
            'billing.address_street_1' => [
                'nullable',
            ],
            'billing.address_street_2' => [
                'nullable',
            ],
            'billing.city' => [
                'nullable',
            ],
            'billing.state' => [
                'nullable',
            ],
            'billing.country_id' => [
                'nullable',
            ],
            'billing.zip' => [
                'nullable',
            ],
            'billing.phone' => [
                'nullable',
            ],
            'billing.fax' => [
                'nullable',
            ],
        ];

        if ($this->isMethod('PUT') && $this->email != null) {
            $rules['email'] = [
                'email',
                'nullable',
                Rule::unique('suppliers')->where('company_id', $this->header('company'))->ignore($this->route('supplier')->id),
            ];
        };

        return $rules;
    }

    public function getSupplierPayload()
    {
        return collect($this->validated())
            ->only([
                'name',
                'email',
                'currency_id',
                'phone',
                'company_name',
                'contact_name',
                'website',
            ])
            ->merge([
                'creator_id' => $this->user()->id,
                'company_id' => $this->header('company'),
            ])
            ->toArray();
    }

    public function getBillingAddress()
    {
        return collect($this->billing)
            ->only(['name', 'address_street_1', 'address_street_2', 'city', 'state', 'country_id', 'zip', 'phone', 'fax'])
            ->merge([
                'type' => Address::BILLING_TYPE
            ])
            ->toArray();
    }

    public function hasAddress(array $address)
    {
        $data = Arr::where($address, function ($value, $key) {
            return isset($value);
        });

        return $data;
    }
}
