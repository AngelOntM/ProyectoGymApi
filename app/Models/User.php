<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'date_of_birth',
        'rol_id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'id', 'user_id');
    }

    public function memberships()
    {
        return $this->belongsToMany(Membership::class, 'user_membership', 'user_id', 'membership_id')
                    ->withPivot('start_date', 'end_date', 'registration_group_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'id', 'user_id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'id', 'user_id');
    }
}
