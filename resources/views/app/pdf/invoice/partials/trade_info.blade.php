{{-- Clear float from totals table before trade section --}}
<div style="clear: both;"></div>

{{-- Trade / Export Information Section --}}
@php
    $hasTradeInfo = $invoice->incoterm || $invoice->contract_number || $invoice->payment_terms ||
                    $invoice->delivery_lead_time ||
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
                COMPANY INFORMATION
            </td>
        </tr>
        @if ($company->business_registration_number)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Business Reg. No.:</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $company->business_registration_number }}</td>
            @if ($company->tax_id)
            <td style="font-size: 10px; color: #55547A; width: 25%;">Tax ID:</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $company->tax_id }}</td>
            @endif
        </tr>
        @elseif ($company->tax_id)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Tax ID:</td>
            <td style="font-size: 10px; color: #040405; width: 75%;" colspan="3">{{ $company->tax_id }}</td>
        </tr>
        @endif
        @if ($company->bank_name)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Bank Name:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $company->bank_name }}</td>
        </tr>
        @endif
        @if ($company->bank_account_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Bank Account No.:</td>
            <td style="font-size: 10px; color: #040405;">{{ $company->bank_account_number }}</td>
            @if ($company->bank_swift_bic)
            <td style="font-size: 10px; color: #55547A;">SWIFT/BIC:</td>
            <td style="font-size: 10px; color: #040405;">{{ $company->bank_swift_bic }}</td>
            @endif
        </tr>
        @endif
        @if ($company->bank_iban)
        <tr>
            <td style="font-size: 10px; color: #55547A;">IBAN:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $company->bank_iban }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if ($hasTradeInfo)
    <table width="100%" cellspacing="0" border="0" style="margin-top: 6px; border-top: 0.5px solid #EAF1FB; padding-top: 6px;">
        <tr>
            <td style="font-size: 11px; font-weight: bold; color: #55547A; padding-bottom: 4px;" colspan="4">
                TRADE &amp; EXPORT INFORMATION
            </td>
        </tr>
        @if ($invoice->incoterm)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Incoterm:</td>
            <td style="font-size: 10px; color: #040405; font-weight: bold; width: 25%;">{{ $invoice->incoterm }}</td>
            @if ($invoice->country_of_origin)
            <td style="font-size: 10px; color: #55547A; width: 25%;">Country of Origin:</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $invoice->country_of_origin }}</td>
            @endif
        </tr>
        @elseif ($invoice->country_of_origin)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Country of Origin:</td>
            <td style="font-size: 10px; color: #040405; width: 75%;" colspan="3">{{ $invoice->country_of_origin }}</td>
        </tr>
        @endif
        @if ($invoice->payment_terms)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Payment Terms:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $invoice->payment_terms }}</td>
        </tr>
        @endif
        @if ($invoice->delivery_lead_time)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Delivery Lead Time:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $invoice->delivery_lead_time }}</td>
        </tr>
        @endif
        @if ($invoice->shipping_port || $invoice->destination_port)
        <tr>
            @if ($invoice->shipping_port)
            <td style="font-size: 10px; color: #55547A;">Port of Loading:</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->shipping_port }}</td>
            @endif
            @if ($invoice->destination_port)
            <td style="font-size: 10px; color: #55547A;">Port of Destination:</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->destination_port }}</td>
            @endif
        </tr>
        @endif
        @if ($invoice->transport_mode)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Transport Mode:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">
                {{ ucfirst(str_replace('_', ' ', $invoice->transport_mode)) }}
            </td>
        </tr>
        @endif
        @if ($invoice->gross_weight || $invoice->net_weight || $invoice->package_count || $invoice->cbm)
        <tr>
            @if ($invoice->gross_weight)
            <td style="font-size: 10px; color: #55547A;">Gross Weight (kg):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->gross_weight }} kg</td>
            @endif
            @if ($invoice->net_weight)
            <td style="font-size: 10px; color: #55547A;">Net Weight (kg):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->net_weight }} kg</td>
            @endif
        </tr>
        <tr>
            @if ($invoice->package_count)
            <td style="font-size: 10px; color: #55547A;">Number of Packages:</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->package_count }}</td>
            @endif
            @if ($invoice->cbm)
            <td style="font-size: 10px; color: #55547A;">Volume (CBM):</td>
            <td style="font-size: 10px; color: #040405;">{{ $invoice->cbm }} m³</td>
            @endif
        </tr>
        @endif
        @if ($invoice->bl_awb_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">B/L or AWB Number:</td>
            <td style="font-size: 10px; color: #040405; font-weight: bold;" colspan="3">{{ $invoice->bl_awb_number }}</td>
        </tr>
        @endif
        @if ($invoice->contract_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Contract Number:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $invoice->contract_number }}</td>
        </tr>
        @endif
    </table>
    @endif

</div>
@endif
