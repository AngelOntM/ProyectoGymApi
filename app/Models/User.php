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
        'face_image_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id'); // Corregido el parámetro de la clave externa
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'user_id', 'id'); // Corregido el parámetro de la clave externa
    }

    public function generateTwoFactorCode(): void
    {
        $this->timestamps = false;  
        $this->two_factor_code = rand(100000, 999999);  
        $this->two_factor_expires_at = now()->addMinutes(10);  
        $this->save();
    }

    public function resetTwoFactorCode(): void
    {
        $this->timestamps = false;
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    public function userMemberships()
    {
        return $this->hasMany(UserMembership::class, 'user_id', 'id');
    }

    // Método para mostrar las visitas de un usuario específico con sus membresías activas
    public function showUserVisits($userId)
    {
        $visits = Visit::where('user_id', $userId)
            ->with(['user', 'userMemberships' => function ($query) {
                $query->active(); // Usamos el scope `active` para filtrar los userMemberships activos
            }])
            ->get();

        return response()->json($visits, 200);
    }
}
