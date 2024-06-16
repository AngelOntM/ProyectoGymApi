<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $table = 'membership';
    protected $primaryKey = 'id';

    protected $fillable = [
        'membership_type',
        'price',
        'duration_days',
        'size',
        'active',
        'benefits',
        'created_at',
        'updated_at',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_membership', 'membership_id', 'user_id')
                    ->withPivot('start_date', 'end_date', 'registration_group_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'id', 'membership_id');
    }
}
