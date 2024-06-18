<?php

namespace App\DB\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    const TABLE_NAME = 'tokens';

    protected $table = self::TABLE_NAME;
    protected array $defaults = [];
    protected $fillable = [
        'name',
        'user_id',
        'expire_at',
        'token',
        'uses',
        'use_limit',
    ];
    // Add casts to ensure correct types
    protected $casts = [
        'use_limit' => 'integer',
        'uses' => 'integer',
    ];


    /**
     * @return $this
     * @throws Exception
     */
    public function consume(): self
    {
        if ($this->use_limit !== 0 && $this->uses >= $this->use_limit) {
            $this->delete();
            throw new Exception('Logged in with maximum Devices limit reached.!');
        }
        if ($this->expire_at < Carbon::now()->toDateTimeString()) {
            $this->delete();
            throw new Exception('Login Expired!');
        }
        $this->uses = $this->uses + 1;
        $this->save();
        return $this;
    }
    public static function deleteToken(string $tokenValue): ?bool
    {
        try {
            // Find the token by its value
            $token = self::where('token', $tokenValue)->first();

            if (!$token) {
                throw new Exception('Token not found');
            }

            // Delete the token
            return $token->delete();
        } catch (Exception $e) {
            // Handle exceptions
            // You may log the error or take other actions as needed
            throw new Exception('Failed to delete token: ' . $e->getMessage());
        }
    }
}
