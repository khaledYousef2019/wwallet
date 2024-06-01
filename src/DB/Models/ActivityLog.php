<?php

namespace App\DB\Models;

use App\DB\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ActivityLog extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'device_id',
        'action',
        'ip_address',
//        'source',
        'location'
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