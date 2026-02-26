{{-- Trade / Export Information Section --}}
@php
    $hasTradeInfo = $invoice->incoterm || $invoice->contract_number || $invoice->payment_terms ||
                    $invoice->shipping_port || $invoice->destination_port || $invoice->transport_mode ||
                    $invoice->gross_weight || $invoice->net_weight || $invoice->package_count ||
                    $invoice->cbm || $invoice->bl_awb_number || $invoice->country_of_origin;
@endphp

@if ($hasTradeInfo || ($company && ($company->business_registration_number || $company->tax_id || $company->bank_name)))
<div style="margin: 15px 30px; padding: 10px; border: 0.5px solid #EAF1FB; page-break-inside: avoid; background-color: #FBFDFF;">

    {{-- Seller Legal & Banking Info --}}
    @if ($company && ($company->business_registration_number || $company->tax_id || $company->bank_name))
    <table width="100%" cellspacing="0" border="0" style="margin-bottom: 10px;">
        <tr>
            <td style="font-size: 11px; font-weight: bold; color: #55547A; padding-bottom: 4px;" colspan="4">
                {{ strtoupper(__('settings.company_information')) }}
            </td>
        </tr>
        @if ($company->business_registration_number)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">@lang('settings.business_registration_number'):</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $company->business_registration_number }}</td>
            @if ($company->tax_id)
            <td style="font-size: 10px; color: #55547A; width: 25%;">@lang('settings.tax_id'):</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $company->tax_id }}</td>
            @endif
        </tr>
        @elseif ($company->tax_id)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">@lang('settings.tax_id'):</td>
            <td style="font-size: 10px; color: #040405; width: 75%;" colspan="3">{{ $company->tax_id }}</td>
        </tr>
        @endif
        @if ($company->bank_name)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('settings.bank_name'):</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $company->bank_name }}</td>
        </tr>
        @endif
        @if ($company->bank_account_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('settings.bank_account_number'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $company->bank_account_number }}</td>
            @if ($company->bank_swift_bic)
            <td style="font-size: 10px; color: #55547A;">@lang('settings.bank_swift_bic'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $company->bank_swift_bic }}</td>
            @endif
        </tr>
        @endif
        @if ($company->bank_iban)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('settings.bank_iban'):</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $company->bank_iban }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if ($hasTradeInfo)
    <table width="100%" cellspacing="0" border="0" style="margin-top: 6px; border-top: 0.5px solid #EAF1FB; padding-top: 6px;">
        <tr>
            <td style="font-size: 11px; font-weight: bold; color: #55547A; padding-bottom: 4px;" colspan="4">
                {{ strtoupper(__('trade.trade_info')) }}
            </td>
        </tr>
        @if ($invoice->incoterm)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">@lang('trade.incoterm'):</td>
            <td style="font-size: 10px; color: #040405; font-weight: bold; width: 25%;">{{ $invoice->incoterm }}</td>
            @if ($invoice->country_of_origin)
            <td style="font-size: 10px; color: #55547A; width: 25%;">@lang('trade.country_of_origin'):</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $invoice->country_of_origin }}</td>
            @endif
        </tr>
        @elseif ($invoice->country_of_origin)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">@lang('trade.country_of_origin'):</td>
            <td style="font-size: 10px; color: #040405; width: 75%;" colspan="3">{{ $invoice->country_of_origin }}</td>
        </tr>
        @endif
        @if ($invoice->payment_terms)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('trade.payment_terms'):</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $invoice->payment_terms }}</td>
        </tr>
        @endif
        @if ($invoice->delivery_lead_time)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('trade.delivery_lead_time'):</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $invoice->delivery_lead_time }}</td>
        </tr>
        @endif
        @if ($invoice->shipping_port || $invoice->destination_port)
        <tr>
            @if ($invoice->shipping_port)
            <td style="font-size: 10px; color: #55547A;">@lang('trade.shipping_port'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->shipping_port }}</td>
            @endif
            @if ($invoice->destination_port)
            <td style="font-size: 10px; color: #55547A;">@lang('trade.destination_port'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->destination_port }}</td>
            @endif
        </tr>
        @endif
        @if ($invoice->transport_mode)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('trade.transport_mode'):</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">
                @lang('trade.' . $invoice->transport_mode)
            </td>
        </tr>
        @endif
        @if ($invoice->gross_weight || $invoice->net_weight || $invoice->package_count || $invoice->cbm)
        <tr>
            @if ($invoice->gross_weight)
            <td style="font-size: 10px; color: #55547A;">@lang('trade.gross_weight_kg'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->gross_weight }} kg</td>
            @endif
            @if ($invoice->net_weight)
            <td style="font-size: 10px; color: #55547A;">@lang('trade.net_weight_kg'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->net_weight }} kg</td>
            @endif
        </tr>
        <tr>
            @if ($invoice->package_count)
            <td style="font-size: 10px; color: #55547A;">@lang('trade.package_count'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->package_count }}</td>
            @endif
            @if ($invoice->cbm)
            <td style="font-size: 10px; color: #55547A;">@lang('trade.cbm'):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->cbm }} m³</td>
            @endif
        </tr>
        @endif
        @if ($invoice->bl_awb_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('trade.bl_awb_number'):</td>
            <td style="font-size: 10px; color: #040405; font-weight: bold;" colspan="3">{{ $invoice->bl_awb_number }}</td>
        </tr>
        @endif
        @if ($invoice->contract_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">@lang('trade.contract_number'):</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $invoice->contract_number }}</td>
        </tr>
        @endif
    </table>
    @endif

</div>
@endif
