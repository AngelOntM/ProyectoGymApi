<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name', 'description', 'price', 'stock', 'discount',
        'active', 'category_id', 'product_image_path'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function membershipDetails()
    {
        return $this->hasMany(MembershipDetail::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->product_image_path ? Storage::url($this->product_image_path) : null;
    }
}
