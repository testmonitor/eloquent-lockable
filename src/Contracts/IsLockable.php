<?php

namespace TestMonitor\Lockable\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
interface IsLockable
{
    /**
     * Check if the model is currently locked.
     */
    public function isLocked(): bool;

    /**
     * Check if the model is currently unlocked.
     */
    public function isUnlocked(): bool;

    /**
     * Set the lock state of the model.
     */
    public function setLocked(bool $state): self;

    /**
     * Set the model to an unlocked state.
     */
    public function setUnlocked(): self;

    /**
     * Mark the model as locked and save the state.
     */
    public function markLocked(): self;

    /**
     * Mark the model as unlocked and save the state.
     */
    public function markUnlocked(): self;

    /**
     * Execute a callback while the model is locked.
     */
    public function whileLocked(callable $callback): self;

    /**
     * Execute a callback while the model is unlocked.
     */
    public function whileUnlocked(callable $callback): self;

    /**
     * Check if the model can be deleted when locked.
     */
    public function canDeleteWhenLocked(): bool;

    /**
     * Check if the model can be restored while locked.
     */
    public function canRestoreWhileLocked(): bool;

    /**
     * Check if the model can be saved while locked.
     */
    public function canSaveWhileLocked(): bool;

    /**
     * Get the column name used for locking the model.
     */
    public function getLockColumn(): string;

    /**
     * Get the exceptions that should not trigger a lock exception.
     *
     * @return list<string>
     */
    public function getLockExceptions(): array;
}
