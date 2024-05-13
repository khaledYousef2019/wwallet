<?php

namespace App\DB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\DB\Factories\UserFactory;
use App\DB\Models\ActivityLog;
use App\DB\Models\ContactDetails;
use App\DB\Models\PersonalDetails;
use App\DB\Models\TwoFactorAuthentication;

class User extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'reset_code',
        'role',
        'status',
        'language',
    ];

    protected static function newFactory()
    {
        return new UserFactory();
    }

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }
    public function roles()
    {}
    public function hasRole($role){}
    public function personalDetails()
    {
        return $this->hasOne(PersonalDetails::class);
    }

    public function contactDetails()
    {
        return $this->hasOne(ContactDetails::class);
    }
    public function twoFactorAuthentication()
    {
        return $this->hasOne(TwoFactorAuthentication::class);
    }
    public function activityLog()
    {
        return $this->hasMany(ActivityLog::class);
    }



}