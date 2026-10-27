<?php

namespace TestMonitor\Lockable\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TestMonitor\Lockable\Concerns\Lockable;
use TestMonitor\Lockable\Contracts\IsLockable;

class User extends Model implements IsLockable
{
    use HasFactory, Lockable;

    protected $table = 'users';

    protected $guarded = [];
}
