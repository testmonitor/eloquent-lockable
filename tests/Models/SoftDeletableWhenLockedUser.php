<?php

namespace TestMonitor\Lockable\Test\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletableWhenLockedUser extends DeletableWhenLockedUser
{
    use SoftDeletes;
}
