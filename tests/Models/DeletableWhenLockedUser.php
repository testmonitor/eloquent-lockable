<?php

namespace TestMonitor\Lockable\Tests\Models;

class DeletableWhenLockedUser extends User
{
    public function canDeleteWhenLocked(): bool
    {
        return true;
    }
}
