<?php

namespace Crater\Models;

use Carbon\Carbon;
use Crater\Traits\HasCustomFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Supplier extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use HasCustomFieldsTrait;

    protected $guarded = ['id'];

    protected $with = ['currency'];

    protected $appends = ['formattedCreatedAt'];

    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);

        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
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

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function billingAddress()
    {
        return $this->hasOne(Address::class)->where('type', Address::BILLING_TYPE);
    }

    public static function createSupplier($request)
    {
        $supplier = self::create($request->getSupplierPayload());

        if ($request->billing) {
            if ($request->hasAddress($request->billing)) {
                $supplier->addresses()->create($request->getBillingAddress());
            }
        }

        $customFields = $request->customFields;

        if ($customFields) {
            $supplier->addCustomFields($customFields);
        }

        $supplier = self::with('billingAddress', 'fields')->find($supplier->id);

        return $supplier;
    }

    public static function updateSupplier($request, $supplier)
    {
        $supplier->update($request->getSupplierPayload());

        $supplier->addresses()->delete();

        if ($request->billing) {
            if ($request->hasAddress($request->billing)) {
                $supplier->addresses()->create($request->getBillingAddress());
            }
        }

        $customFields = $request->customFields;

        if ($customFields) {
            $supplier->updateCustomFields($customFields);
        }

        $supplier = self::with('billingAddress', 'fields')->find($supplier->id);

        return $supplier;
    }

    public static function deleteSuppliers($ids)
    {
        foreach ($ids as $id) {
            $supplier = self::find($id);

            if ($supplier->expenses()->exists()) {
                $supplier->expenses()->update(['supplier_id' => null]);
            }

            if ($supplier->addresses()->exists()) {
                $supplier->addresses()->delete();
            }

            $supplier->delete();
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
        return $query->where('suppliers.company_id', request()->header('company'));
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', '%'.$term.'%')
                    ->orWhere('email', 'LIKE', '%'.$term.'%')
                    ->orWhere('phone', 'LIKE', '%'.$term.'%');
            });
        }
    }

    public function scopeWhereContactName($query, $contactName)
    {
        return $query->where('contact_name', 'LIKE', '%'.$contactName.'%');
    }

    public function scopeWhereDisplayName($query, $displayName)
    {
        return $query->where('name', 'LIKE', '%'.$displayName.'%');
    }

    public function scopeWherePhone($query, $phone)
    {
        return $query->where('phone', 'LIKE', '%'.$phone.'%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('contact_name')) {
            $query->whereContactName($filters->get('contact_name'));
        }

        if ($filters->get('display_name')) {
            $query->whereDisplayName($filters->get('display_name'));
        }

        if ($filters->get('phone')) {
            $query->wherePhone($filters->get('phone'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }
}
