<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_name',
        'description',
        'price',
        'stock_quantity',
        'discount',
        'created_at',
        'updated_at',
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id', 'product_id');
    }
}

