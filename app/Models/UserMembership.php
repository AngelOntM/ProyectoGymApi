<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'membership_id', 'start_date', 'end_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function membershipDetail()
    {
        return $this->belongsTo(MembershipDetail::class, 'membership_id');
    }

    public function membershipCodes()
    {
        return $this->hasMany(MembershipCode::class, 'user_membership_id');
    }
}

