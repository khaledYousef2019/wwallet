<?php

namespace App\DB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoFactorAuthentication extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'two_factor';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'g2f_enabled',
        'google2fa_secret',
    ];

//    protected static function newFactory()
//    {
//        return new UserFactory();
//    }

//    public function tokens()
//    {
//        return $this->hasOne(User::class);
//    }

}