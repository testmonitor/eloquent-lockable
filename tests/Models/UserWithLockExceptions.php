<?php

namespace TestMonitor\Lockable\Tests\Models;

class UserWithLockExceptions extends User
{
    public function getLockExceptions(): array
    {
        return ['note'];
    }
}
