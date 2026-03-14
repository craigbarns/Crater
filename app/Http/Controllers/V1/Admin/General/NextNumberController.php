<?php

namespace Crater\Http\Controllers\V1\Admin\General;

use Crater\Http\Controllers\Controller;
use Crater\Models\Estimate;
use Crater\Models\Invoice;
use Crater\Models\Payment;
use Crater\Models\SupplierPayment;
use Crater\Services\SerialNumberFormatter;
use Illuminate\Http\Request;

class NextNumberController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Invoice $invoice, Estimate $estimate, Payment $payment)
    {
        $key = $request->key;
        $nextNumber = null;
        $serial = (new SerialNumberFormatter())
            ->setCompany($request->header('company'))
            ->setCustomer($request->userId);

        try {
            switch ($key) {
                case 'invoice':
                    $nextNumber = $serial->setModel($invoice)
                        ->setModelObject($request->model_id)
                        ->getNextNumber();

                    break;

                case 'estimate':
                    $nextNumber = $serial->setModel($estimate)
                        ->setModelObject($request->model_id)
                        ->getNextNumber();

                    break;

                case 'payment':
                    $nextNumber = $serial->setModel($payment)
                        ->setModelObject($request->model_id)
                        ->getNextNumber();

                    break;

                case 'supplier_payment':
                    $companyId = $request->header('company');
                    $last = SupplierPayment::where('company_id', $companyId)
                        ->orderBy('id', 'desc')
                        ->first();
                    $nextSeq = $last ? ((int) preg_replace('/[^0-9]/', '', $last->payment_number)) + 1 : 1;
                    $nextNumber = 'SP-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

                    break;

                default:
                    return;
            }
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'nextNumber' => $nextNumber,
        ]);
    }
}
