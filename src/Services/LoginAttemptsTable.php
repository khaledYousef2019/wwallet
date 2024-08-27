<?php

namespace App\Services;

use Carbon\Carbon;
use Swoole\Table;

class LoginAttemptsTable
{
    protected static ?LoginAttemptsTable $instance = null;
    protected static Table $table;

    private function __construct()
    {
        self::$table = $this->createTable();
    }

    public static function getInstance(): LoginAttemptsTable
    {
        if (self::$instance === null) {
            self::$instance = new LoginAttemptsTable;
        }
        return self::$instance;
    }

    protected function createTable(): Table
    {
        $table = new Table(1024);
        $table->column('attempts', Table::TYPE_INT, 4);
        $table->column('ttl', Table::TYPE_INT, 8); // Expiration timestamp
        $table->create();
        return $table;
    }

    public function incr(string $key, int $increment = 1, int $ttl = 60): int
    {
        if ($this->has($key)) {
            $row = self::$table->get($key);
            $attempts = $row['attempts'] + $increment;
            self::$table->set($key, [
                'attempts' => $attempts,
                'ttl' => $row['ttl'],
            ]);
        } else {
            $attempts = $increment;
            self::$table->set($key, [
                'attempts' => $attempts,
                'ttl' => Carbon::now()->addSeconds($ttl)->getTimestamp(),
            ]);
        }
        return $attempts;
    }

    public function get(string $key): ?int
    {
        if ($this->has($key)) {
            $row = self::$table->get($key);
            return $row['attempts'];
        }
        return null;
    }

    public function reset(string $key): void
    {
        self::$table->del($key);
    }

    public function has(string $key): bool
    {
        if (self::$table->exists($key)) {
            $row = self::$table->get($key);
            if (Carbon::now()->getTimestamp() < $row['ttl']) {
                return true;
            }
            // Expired, remove the record
            $this->reset($key);
        }
        return false;
    }
}
