<?php

namespace TestMonitor\Lockable\Test\Models;

use Illuminate\Database\Eloquent\Model;
use TestMonitor\Lockable\Concerns\Lockable;
use TestMonitor\Lockable\Contracts\IsLockable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model implements IsLockable
{
    use HasFactory, Lockable;

    protected $table = 'users';

    protected $guarded = [];

    public function getLockExceptions(): array
    {
        return ['note'];
    }
}
