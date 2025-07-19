<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //
    protected $fillable = [
        'user_id',
        'customer_id',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesDetail()
    {
        return $this->hasMany(SaleDetails::class);
    }
}
