<?php

namespace App\DB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Validator\Constraints\Currency;

class Wallet extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'wallets';
    protected $fillable = [
        'user_id',
        'symbol',
        'chain',
        'balance',
        'address',
        'activation_trx',
        'network',
        'status'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function transactions(){}
    public function currency(){
        return $this->belongsTo(Currency::class);
    }

}