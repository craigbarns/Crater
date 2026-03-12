<?php

namespace Crater\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteSupplierPaymentsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ids' => [
                'required',
            ],
            'ids.*' => [
                'required',
            ],
        ];
    }
}
