<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'duration_days', 'size'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function userMemberships()
    {
        return $this->hasMany(UserMembership::class, 'membership_id');
    }
}
