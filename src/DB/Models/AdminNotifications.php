<?php

namespace App\DB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdminNotifications extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'admin_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'read_status',
        'click_url',
        'message'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::Class, 'id', 'user_id');
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