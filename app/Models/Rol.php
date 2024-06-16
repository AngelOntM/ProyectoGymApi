<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rols';
    protected $primaryKey = 'id';

    protected $fillable = [
        'rol_name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id', 'rol_id');
    }
}
