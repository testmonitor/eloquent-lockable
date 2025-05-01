<?php

namespace TestMonitor\Lockable\Test\Models;

class DeletableWhenLockedUser extends User
{
    public function canDeleteWhenLocked(): bool
    {
        return true;
    }
}
