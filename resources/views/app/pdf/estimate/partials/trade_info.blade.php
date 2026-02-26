{{-- Trade / Export Information Section for Estimates --}}
@php
    $hasTradeInfo = $estimate->incoterm || $estimate->contract_number || $estimate->payment_terms ||
                    $estimate->shipping_port || $estimate->destination_port || $estimate->transport_mode ||
                    $estimate->gross_weight || $estimate->net_weight || $estimate->package_count ||
                    $estimate->cbm || $estimate->country_of_origin;
@endphp

@if ($hasTradeInfo || ($company && ($company->business_registration_number || $company->tax_id || $company->bank_name)))
<div style="margin: 15px 30px; padding: 10px; border: 0.5px solid #E8E8E8; page-break-inside: avoid;">

    {{-- Seller Legal & Banking Info --}}
    @if ($company && ($company->business_registration_number || $company->tax_id || $company->bank_name))
    <table width="100%" cellspacing="0" border="0" style="margin-bottom: 10px;">
        <tr>
            <td style="font-size: 11px; font-weight: bold; color: #55547A; padding-bottom: 4px;" colspan="4">
                Seller Information
            </td>
        </tr>
        @if ($company->business_registration_number)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Business Reg. No. (营业执照):</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $company->business_registration_number }}</td>
            @if ($company->tax_id)
            <td style="font-size: 10px; color: #55547A; width: 25%;">Tax ID (税号):</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $company->tax_id }}</td>
            @endif
        </tr>
        @elseif ($company->tax_id)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Tax ID (税号):</td>
            <td style="font-size: 10px; color: #040405; width: 75%;" colspan="3">{{ $company->tax_id }}</td>
        </tr>
        @endif
        @if ($company->bank_name)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Bank:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $company->bank_name }}</td>
        </tr>
        @endif
        @if ($company->bank_account_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Account No.:</td>
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
    <table width="100%" cellspacing="0" border="0" style="margin-top: 6px;">
        <tr>
            <td style="font-size: 11px; font-weight: bold; color: #55547A; padding-bottom: 4px;" colspan="4">
                Trade &amp; Shipping Information
            </td>
        </tr>
        @if ($estimate->incoterm)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Incoterm:</td>
            <td style="font-size: 10px; color: #040405; font-weight: bold; width: 25%;">{{ $estimate->incoterm }}</td>
            @if ($estimate->country_of_origin)
            <td style="font-size: 10px; color: #55547A; width: 25%;">Country of Origin:</td>
            <td style="font-size: 10px; color: #040405; width: 25%;">{{ $estimate->country_of_origin }}</td>
            @endif
        </tr>
        @elseif ($estimate->country_of_origin)
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Country of Origin:</td>
            <td style="font-size: 10px; color: #040405; width: 75%;" colspan="3">{{ $estimate->country_of_origin }}</td>
        </tr>
        @endif
        @if ($estimate->payment_terms)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Payment Terms:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $estimate->payment_terms }}</td>
        </tr>
        @endif
        @if ($estimate->delivery_lead_time)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Delivery Lead Time:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $estimate->delivery_lead_time }}</td>
        </tr>
        @endif
        @if ($estimate->shipping_port || $estimate->destination_port)
        <tr>
            @if ($estimate->shipping_port)
            <td style="font-size: 10px; color: #55547A;">Port of Loading:</td>
            <td style="font-size: 10px; color: #040405;">{{ $estimate->shipping_port }}</td>
            @endif
            @if ($estimate->destination_port)
            <td style="font-size: 10px; color: #55547A;">Port of Destination:</td>
            <td style="font-size: 10px; color: #040405;">{{ $estimate->destination_port }}</td>
            @endif
        </tr>
        @endif
        @if ($estimate->transport_mode)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Transport Mode:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ ucfirst($estimate->transport_mode) }}</td>
        </tr>
        @endif
        @if ($estimate->gross_weight || $estimate->net_weight || $estimate->package_count || $estimate->cbm)
        <tr>
            @if ($estimate->gross_weight)
            <td style="font-size: 10px; color: #55547A;">Gross Weight:</td>
            <td style="font-size: 10px; color: #040405;">{{ $estimate->gross_weight }} kg</td>
            @endif
            @if ($estimate->net_weight)
            <td style="font-size: 10px; color: #55547A;">Net Weight:</td>
            <td style="font-size: 10px; color: #040405;">{{ $estimate->net_weight }} kg</td>
            @endif
        </tr>
        <tr>
            @if ($estimate->package_count)
            <td style="font-size: 10px; color: #55547A;">Packages:</td>
            <td style="font-size: 10px; color: #040405;">{{ $estimate->package_count }}</td>
            @endif
            @if ($estimate->cbm)
            <td style="font-size: 10px; color: #55547A;">Volume (CBM):</td>
            <td style="font-size: 10px; color: #040405;">{{ $estimate->cbm }} m³</td>
            @endif
        </tr>
        @endif
        @if ($estimate->contract_number)
        <tr>
            <td style="font-size: 10px; color: #55547A;">Contract No.:</td>
            <td style="font-size: 10px; color: #040405;" colspan="3">{{ $estimate->contract_number }}</td>
        </tr>
        @endif
    </table>

    {{-- Buyer VAT Number --}}
    @if ($estimate->customer && $estimate->customer->vat_number)
    <table width="100%" cellspacing="0" border="0" style="margin-top: 6px;">
        <tr>
            <td style="font-size: 10px; color: #55547A; width: 25%;">Buyer VAT No.:</td>
            <td style="font-size: 10px; color: #040405;">{{ $estimate->customer->vat_number }}</td>
        </tr>
    </table>
    @endif
    @endif

</div>
@endif
