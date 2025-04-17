<?php

namespace TestMonitor\Lockable\Contracts;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
*/
interface IsLockable
{
    public function isLocked(): bool;

    public function isUnlocked(): bool;

    public function lock(): self;

    public function unlock(): self;

    public function whileLocked(callable $callback): self;

    public function whileUnlocked(callable $callback): self;

    public function getLockColumn(): string;
}
