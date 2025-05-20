<?php

namespace TestMonitor\Lockable\Test\Models;

class UserWithLockExceptions extends User
{
    public function getLockExceptions(): array
    {
        return ['note'];
    }
}
