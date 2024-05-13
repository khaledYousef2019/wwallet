<?php

namespace App\DB\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'tokens';
    protected array $defaults = [];
    protected $fillable = [
        'name',
        'user_id',
        'expire_at',
        'token',
        'uses',
        'use_limit',
    ];
    private mixed $use_limit;
    private int $uses;

    /**
     * @return $this
     * @throws Exception
     */
    public function consume(): self
    {
        if ($this->use_limit !== 0 && $this->uses >= $this->use_limit) {
            $this->delete();
            throw new Exception('Token already consumed!');
        }

        $this->uses = $this->uses + 1;
        $this->save();
        return $this;
    }
}
