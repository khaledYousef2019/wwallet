<?php

namespace App\DB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'symbol',
        'name',
        'usd',
        'status',
        'contract',
        'image',
        'is_withdrawal',
        'is_deposit',
        'description'
    ];

}