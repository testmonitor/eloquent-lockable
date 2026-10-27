<?php

namespace TestMonitor\Lockable\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletableWhenLockedUser extends DeletableWhenLockedUser
{
    use SoftDeletes;
}
