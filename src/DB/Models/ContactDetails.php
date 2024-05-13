<?php

namespace App\DB\Models;

use App\DB\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactDetails extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'contact_details';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'country',
        'country_code',
        'phone',
        'phone_verified',
    ];

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

//    protected static function newFactory()
//    {
//        return new UserFactory();
//    }

//    public function tokens()
//    {
//        return $this->hasOne(User::class);
//    }

}