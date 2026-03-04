<?php

namespace Crater\Http\Controllers\V1\Admin\Estimate;

use Carbon\Carbon;
use Crater\Http\Controllers\Controller;
use Crater\Http\Resources\EstimateResource;
use Crater\Models\Estimate;
use Crater\Services\SerialNumberFormatter;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class CloneEstimateController extends Controller
{
    /**
     * Clone a specific estimate into a new draft estimate.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, Estimate $estimate)
    {
        $this->authorize('create', Estimate::class);

        $date = Carbon::now();

        $serial = (new SerialNumberFormatter())
            ->setModel($estimate)
            ->setCompany($estimate->company_id)
            ->setCustomer($estimate->customer_id)
            ->setNextNumbers();

        $exchange_rate = $estimate->exchange_rate;

        $newEstimate = Estimate::create([
            'estimate_date' => $date->format('Y-m-d'),
            'expiry_date' => $date->addDays(30)->format('Y-m-d'),
            'estimate_number' => $serial->getNextNumber(),
            'sequence_number' => $serial->nextSequenceNumber,
            'customer_sequence_number' => $serial->nextCustomerSequenceNumber,
            'reference_number' => $estimate->reference_number,
            'customer_id' => $estimate->customer_id,
            'company_id' => $request->header('company'),
            'template_name' => $estimate->template_name,
            'status' => Estimate::STATUS_DRAFT,
            'sub_total' => $estimate->sub_total,
            'discount' => $estimate->discount,
            'discount_type' => $estimate->discount_type,
            'discount_val' => $estimate->discount_val,
            'total' => $estimate->total,
            'tax_per_item' => $estimate->tax_per_item,
            'discount_per_item' => $estimate->discount_per_item,
            'tax' => $estimate->tax,
            'notes' => $estimate->notes,
            'exchange_rate' => $exchange_rate,
            'base_total' => $estimate->total * $exchange_rate,
            'base_discount_val' => $estimate->discount_val * $exchange_rate,
            'base_sub_total' => $estimate->sub_total * $exchange_rate,
            'base_tax' => $estimate->tax * $exchange_rate,
            'currency_id' => $estimate->currency_id,
            'sales_tax_type' => $estimate->sales_tax_type,
            'sales_tax_address_type' => $estimate->sales_tax_address_type,
            'contract_number' => $estimate->contract_number,
            'incoterm' => $estimate->incoterm,
            'payment_terms' => $estimate->payment_terms,
            'delivery_lead_time' => $estimate->delivery_lead_time,
            'shipping_port' => $estimate->shipping_port,
            'destination_port' => $estimate->destination_port,
            'transport_mode' => $estimate->transport_mode,
            'gross_weight' => $estimate->gross_weight,
            'net_weight' => $estimate->net_weight,
            'package_count' => $estimate->package_count,
            'cbm' => $estimate->cbm,
            'bl_awb_number' => $estimate->bl_awb_number,
            'country_of_origin' => $estimate->country_of_origin,
        ]);

        $newEstimate->unique_hash = Hashids::connection(Estimate::class)->encode($newEstimate->id);
        $newEstimate->save();
        $estimate->load('items.taxes');

        $estimateItems = $estimate->items->toArray();

        foreach ($estimateItems as $estimateItem) {
            $estimateItem['company_id'] = $request->header('company');
            $estimateItem['name'] = $estimateItem['name'];
            $estimateItem['exchange_rate'] = $exchange_rate;
            $estimateItem['base_price'] = $estimateItem['price'] * $exchange_rate;
            $estimateItem['base_discount_val'] = $estimateItem['discount_val'] * $exchange_rate;
            $estimateItem['base_tax'] = $estimateItem['tax'] * $exchange_rate;
            $estimateItem['base_total'] = $estimateItem['total'] * $exchange_rate;

            $item = $newEstimate->items()->create($estimateItem);

            if (array_key_exists('taxes', $estimateItem) && $estimateItem['taxes']) {
                foreach ($estimateItem['taxes'] as $tax) {
                    $tax['company_id'] = $request->header('company');

                    if ($tax['amount']) {
                        $item->taxes()->create($tax);
                    }
                }
            }
        }

        if ($estimate->taxes) {
            foreach ($estimate->taxes->toArray() as $tax) {
                $tax['company_id'] = $request->header('company');
                $newEstimate->taxes()->create($tax);
            }
        }

        if ($estimate->fields()->exists()) {
            $customFields = [];

            foreach ($estimate->fields as $data) {
                $customFields[] = [
                    'id' => $data->custom_field_id,
                    'value' => $data->defaultAnswer
                ];
            }

            $newEstimate->addCustomFields($customFields);
        }

        return new EstimateResource($newEstimate);
    }
}
