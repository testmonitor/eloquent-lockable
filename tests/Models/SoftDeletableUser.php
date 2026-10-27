<?php

namespace TestMonitor\Lockable\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletableUser extends User
{
    use SoftDeletes;
}
