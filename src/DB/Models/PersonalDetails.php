<?php

namespace App\DB\Models;

use App\DB\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalDetails extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'personal_details';

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'photo',
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