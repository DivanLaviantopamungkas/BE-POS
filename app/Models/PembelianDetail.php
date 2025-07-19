<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    protected $table = 'purchases_detail';
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'remaining_stock',
        'price',
        'subtotal'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function produk()
    {
        return $this->belongsTo(Product::class);
    }
}
