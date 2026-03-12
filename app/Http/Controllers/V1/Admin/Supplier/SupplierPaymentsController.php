<?php

namespace Crater\Http\Controllers\V1\Admin\Supplier;

use Crater\Http\Controllers\Controller;
use Crater\Http\Requests\DeleteSupplierPaymentsRequest;
use Crater\Http\Requests\SupplierPaymentRequest;
use Crater\Http\Resources\SupplierPaymentResource;
use Crater\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierPaymentsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SupplierPayment::class);

        $limit = $request->has('limit') ? $request->limit : 10;

        $supplierPayments = SupplierPayment::with('supplier', 'paymentMethod', 'currency', 'creator')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_payments.supplier_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'supplier_payments.payment_method_id')
            ->whereCompany()
            ->applyFilters($request->all())
            ->select(
                'supplier_payments.*',
                'suppliers.name as supplier_name',
                'payment_methods.name as payment_mode_name'
            )
            ->paginateData($limit);

        return (SupplierPaymentResource::collection($supplierPayments))
            ->additional(['meta' => [
                'supplier_payment_total_count' => SupplierPayment::whereCompany()->count(),
            ]]);
    }

    public function store(SupplierPaymentRequest $request)
    {
        $this->authorize('create', SupplierPayment::class);

        $supplierPayment = SupplierPayment::createSupplierPayment($request);

        return new SupplierPaymentResource($supplierPayment);
    }

    public function show(SupplierPayment $supplierPayment)
    {
        $this->authorize('view', $supplierPayment);

        return new SupplierPaymentResource($supplierPayment);
    }

    public function update(SupplierPaymentRequest $request, SupplierPayment $supplierPayment)
    {
        $this->authorize('update', $supplierPayment);

        $supplierPayment = SupplierPayment::updateSupplierPayment($request, $supplierPayment);

        return new SupplierPaymentResource($supplierPayment);
    }

    public function delete(DeleteSupplierPaymentsRequest $request)
    {
        $this->authorize('delete multiple supplier payments');

        SupplierPayment::deleteSupplierPayments($request->ids);

        return response()->json([
            'success' => true,
        ]);
    }
}
