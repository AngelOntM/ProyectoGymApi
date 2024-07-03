<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    /**
     * Generate a unique membership code containing only alphanumeric characters.
     *
     * @param int $length Desired length of the code
     * @return string
     */
    public static function generateMembershipCode($length = 10)
    {
        // Characters allowed in the code
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        $maxIndex = strlen($characters) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $maxIndex)];
        }

        // Ensure code is unique in the database
        while (self::where('code', $code)->exists()) {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, $maxIndex)];
            }
        }

        return $code;
    }
}
