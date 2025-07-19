<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $appends = ['stock'];
    protected $fillable = [
        'name',
        'category_id',
        'price',
        'description'
    ];

    //relasi 
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function detail()
    {
        return $this->hasMany(PembelianDetail::class, 'product_id');
    }

    // public function salsesDetail()
    // {
    //     return $this->hasMany()
    // }

    public function getStockAttribute()
    {
        return $this->detail->sum('remaining_stock');
    }
}
