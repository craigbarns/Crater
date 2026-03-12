<?php

namespace Crater\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['formattedCreatedAt', 'formattedPaymentDate'];

    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);

        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function getFormattedPaymentDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);

        return Carbon::parse($this->payment_date)->format($dateFormat);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public static function createSupplierPayment($request)
    {
        $supplierPayment = self::create($request->getSupplierPaymentPayload());

        return $supplierPayment;
    }

    public static function updateSupplierPayment($request, $supplierPayment)
    {
        $supplierPayment->update($request->getSupplierPaymentPayload());

        return $supplierPayment;
    }

    public static function deleteSupplierPayments($ids)
    {
        foreach ($ids as $id) {
            $supplierPayment = self::find($id);

            if ($supplierPayment) {
                $supplierPayment->delete();
            }
        }

        return true;
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            return $query->get();
        }

        return $query->paginate($limit);
    }

    public function scopeWhereCompany($query)
    {
        return $query->where('supplier_payments.company_id', request()->header('company'));
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->where(function ($query) use ($term) {
                $query->where('payment_number', 'LIKE', '%'.$term.'%')
                    ->orWhereHas('supplier', function ($query) use ($term) {
                        $query->where('name', 'LIKE', '%'.$term.'%');
                    });
            });
        }
    }

    public function scopeWhereSupplier($query, $supplier_id)
    {
        if ($supplier_id) {
            return $query->where('supplier_payments.supplier_id', $supplier_id);
        }
    }

    public function scopeWherePaymentMethod($query, $payment_method_id)
    {
        if ($payment_method_id) {
            return $query->where('supplier_payments.payment_method_id', $payment_method_id);
        }
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopePaymentsBetween($query, $start, $end)
    {
        if ($start && $end) {
            $query->whereBetween('supplier_payments.payment_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        }
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('supplier_id')) {
            $query->whereSupplier($filters->get('supplier_id'));
        }

        if ($filters->get('payment_method_id')) {
            $query->wherePaymentMethod($filters->get('payment_method_id'));
        }

        if ($filters->get('payment_number')) {
            $query->where('payment_number', 'LIKE', '%'.$filters->get('payment_number').'%');
        }

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('Y-m-d', $filters->get('from_date'));
            $end = Carbon::createFromFormat('Y-m-d', $filters->get('to_date'));
            $query->paymentsBetween($start, $end);
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'payment_date';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'desc';
            $query->whereOrder($field, $orderBy);
        }
    }
}
