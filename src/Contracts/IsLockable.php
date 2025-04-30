<?php

namespace TestMonitor\Lockable\Contracts;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface IsLockable
{
    public function isLocked(): bool;

    public function isUnlocked(): bool;

    public function setLocked(bool $state): self;

    public function setUnlocked(): self;

    public function markLocked(): self;

    public function markUnlocked(): self;

    public function whileLocked(callable $callback): self;

    public function whileUnlocked(callable $callback): self;

    public function getLockColumn(): string;
}
