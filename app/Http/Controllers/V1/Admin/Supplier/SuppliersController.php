<?php

namespace Crater\Http\Controllers\V1\Admin\Supplier;

use Crater\Http\Controllers\Controller;
use Crater\Http\Requests\DeleteSuppliersRequest;
use Crater\Http\Requests\SupplierRequest;
use Crater\Http\Resources\SupplierResource;
use Crater\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $limit = $request->has('limit') ? $request->limit : 10;

        $suppliers = Supplier::with('creator')
            ->whereCompany()
            ->applyFilters($request->all())
            ->select('suppliers.*')
            ->paginateData($limit);

        return (SupplierResource::collection($suppliers))
            ->additional(['meta' => [
                'supplier_total_count' => Supplier::whereCompany()->count(),
            ]]);
    }

    public function store(SupplierRequest $request)
    {
        $this->authorize('create', Supplier::class);

        $supplier = Supplier::createSupplier($request);

        return new SupplierResource($supplier);
    }

    public function show(Supplier $supplier)
    {
        $this->authorize('view', $supplier);

        return new SupplierResource($supplier);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $supplier = Supplier::updateSupplier($request, $supplier);

        return new SupplierResource($supplier);
    }

    public function delete(DeleteSuppliersRequest $request)
    {
        $this->authorize('delete multiple suppliers');

        Supplier::deleteSuppliers($request->ids);

        return response()->json([
            'success' => true,
        ]);
    }
}
