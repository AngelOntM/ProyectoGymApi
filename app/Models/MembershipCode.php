<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'user_membership_id', 'available'
    ];

    public function userMembership()
    {
        return $this->belongsTo(UserMembership::class, 'user_membership_id');
    }
}
