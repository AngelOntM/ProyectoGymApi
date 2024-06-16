<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMembership extends Model
{
    use HasFactory;

    protected $table = 'user_membership';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'membership_id',
        'start_date',
        'end_date',
        'registration_group_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class, 'membership_id', 'id');
    }
}
