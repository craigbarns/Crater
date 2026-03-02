<?php

namespace Crater\Http\Controllers\V1\Admin\Estimate;

use Carbon\Carbon;
use App;
use Crater\Http\Controllers\Controller;
use Crater\Http\Resources\InvoiceResource;
use Crater\Models\CompanySetting;
use Crater\Models\Estimate;
use Crater\Models\Invoice;
use Crater\Services\SerialNumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class CreateDepositInvoiceController extends Controller
{
    /**
     * Create a deposit invoice from an estimate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Crater\Models\Estimate  $estimate
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Estimate $estimate)
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'deposit_type' => 'required|in:percentage,fixed',
            'deposit_value' => 'required|numeric|min:0.01',
        ]);

        $estimate->load(['items', 'items.taxes', 'customer', 'taxes']);

        // Calculate deposit percentage and amounts
        if ($request->deposit_type === 'percentage') {
            $percentage = (float) $request->deposit_value;
            $depositSubTotal = (int) round($estimate->sub_total * $percentage / 100);
            $depositTax = (int) round($estimate->tax * $percentage / 100);
            $depositTotal = $depositSubTotal + $depositTax;
        } else {
            // Fixed amount is TTC (total including tax)
            $depositTotal = (int) round($request->deposit_value);
            $percentage = $estimate->total > 0
                ? round(($depositTotal / $estimate->total) * 100, 2)
                : 0;
            // Calculate proportional sub_total and tax
            if ($estimate->total > 0) {
                $depositSubTotal = (int) round($estimate->sub_total * $percentage / 100);
                $depositTax = $depositTotal - $depositSubTotal;
            } else {
                $depositSubTotal = $depositTotal;
                $depositTax = 0;
            }
        }

        // Validate: deposit cannot exceed remaining amount
        $existingDepositsTotal = Invoice::where('estimate_id', $estimate->id)
            ->where('invoice_type', Invoice::TYPE_DEPOSIT)
            ->sum('total');

        if (($existingDepositsTotal + $depositTotal) > $estimate->total) {
            return response()->json([
                'error' => 'deposit_exceeds_estimate_total',
                'message' => 'Total deposits would exceed estimate total',
            ], 422);
        }

        // Due date settings
        $invoice_date = Carbon::now();
        $due_date = null;

        $dueDateEnabled = CompanySetting::getSetting(
            'invoice_set_due_date_automatically',
            $request->header('company')
        );

        if ($dueDateEnabled === 'YES') {
            $dueDateDays = CompanySetting::getSetting(
                'invoice_due_date_days',
                $request->header('company')
            );
            $due_date = Carbon::now()->addDays($dueDateDays)->format('Y-m-d');
        }

        // Serial number
        $serial = (new SerialNumberFormatter())
            ->setModel(new Invoice())
            ->setCompany($estimate->company_id)
            ->setCustomer($estimate->customer_id)
            ->setNextNumbers();

        $templateName = $estimate->getInvoiceTemplateName();
        $exchange_rate = $estimate->exchange_rate;

        // Set locale for translated item names
        $locale = CompanySetting::getSetting('language', $estimate->company_id);
        App::setLocale($locale);

        // Create the deposit invoice
        $invoice = Invoice::create([
            'creator_id' => Auth::id(),
            'invoice_date' => $invoice_date->format('Y-m-d'),
            'due_date' => $due_date,
            'invoice_number' => $serial->getNextNumber(),
            'sequence_number' => $serial->nextSequenceNumber,
            'customer_sequence_number' => $serial->nextCustomerSequenceNumber,
            'reference_number' => $estimate->estimate_number,
            'customer_id' => $estimate->customer_id,
            'company_id' => $request->header('company'),
            'template_name' => $templateName,
            'status' => Invoice::STATUS_DRAFT,
            'paid_status' => Invoice::STATUS_UNPAID,
            'sub_total' => $depositSubTotal,
            'discount' => 0,
            'discount_type' => 'fixed',
            'discount_val' => 0,
            'total' => $depositTotal,
            'due_amount' => $depositTotal,
            'tax_per_item' => 'NO',
            'discount_per_item' => 'NO',
            'tax' => $depositTax,
            'notes' => null,
            'exchange_rate' => $exchange_rate,
            'base_discount_val' => 0,
            'base_sub_total' => $depositSubTotal * $exchange_rate,
            'base_total' => $depositTotal * $exchange_rate,
            'base_tax' => $depositTax * $exchange_rate,
            'base_due_amount' => $depositTotal * $exchange_rate,
            'currency_id' => $estimate->currency_id,
            'sales_tax_type' => $estimate->sales_tax_type,
            'sales_tax_address_type' => $estimate->sales_tax_address_type,
            // Deposit-specific fields
            'invoice_type' => Invoice::TYPE_DEPOSIT,
            'estimate_id' => $estimate->id,
            'deposit_percentage' => $percentage,
        ]);

        $invoice->unique_hash = Hashids::connection(Invoice::class)->encode($invoice->id);
        $invoice->save();

        // Create a single line item for the deposit
        $itemName = $request->deposit_type === 'percentage'
            ? __('pdf_deposit_percentage_item', ['percentage' => $percentage, 'number' => $estimate->estimate_number])
            : __('pdf_deposit_fixed_item', ['number' => $estimate->estimate_number]);

        $itemDescription = __('pdf_deposit_item_description', ['number' => $estimate->estimate_number]);

        $invoice->items()->create([
            'name' => $itemName,
            'description' => $itemDescription,
            'quantity' => 1,
            'price' => $depositSubTotal,
            'total' => $depositSubTotal,
            'discount_type' => 'fixed',
            'discount' => 0,
            'discount_val' => 0,
            'tax' => $depositTax,
            'company_id' => $request->header('company'),
            'exchange_rate' => $exchange_rate,
            'base_price' => $depositSubTotal * $exchange_rate,
            'base_discount_val' => 0,
            'base_tax' => $depositTax * $exchange_rate,
            'base_total' => $depositSubTotal * $exchange_rate,
        ]);

        // Copy taxes proportionally from estimate
        if ($estimate->taxes->count() > 0) {
            foreach ($estimate->taxes as $tax) {
                $proportionalAmount = (int) round($tax->amount * $percentage / 100);
                $invoice->taxes()->create([
                    'tax_type_id' => $tax->tax_type_id,
                    'name' => $tax->name,
                    'amount' => $proportionalAmount,
                    'percent' => $tax->percent,
                    'compound_tax' => $tax->compound_tax,
                    'company_id' => $request->header('company'),
                    'exchange_rate' => $exchange_rate,
                    'base_amount' => $proportionalAmount * $exchange_rate,
                    'currency_id' => $estimate->currency_id,
                ]);
            }
        }

        $invoice = Invoice::with('items', 'customer', 'taxes')->find($invoice->id);

        return new InvoiceResource($invoice);
    }
}
