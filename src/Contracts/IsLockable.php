<?php

namespace TestMonitor\Lockable\Contracts;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface IsLockable
{
    /**
     * Check if the model is currently locked.
     *
     * @return bool
     */
    public function isLocked(): bool;

    /**
     * Check if the model is currently unlocked.
     *
     * @return bool
     */
    public function isUnlocked(): bool;

    /**
     * Set the lock state of the model.
     *
     * @param bool $state
     * @return self
     */
    public function setLocked(bool $state): self;

    /**
     * Set the model to an unlocked state.
     *
     * @return self
     */
    public function setUnlocked(): self;

    /**
     * Mark the model as locked and save the state.
     *
     * @return self
     */
    public function markLocked(): self;

    /**
     * Mark the model as unlocked and save the state.
     *
     * @return self
     */
    public function markUnlocked(): self;

    /**
     * Execute a callback while the model is locked.
     *
     * @param callable $callback
     * @return self
     */
    public function whileLocked(callable $callback): self;

    /**
     * Execute a callback while the model is unlocked.
     *
     * @param callable $callback
     * @return self
     */
    public function whileUnlocked(callable $callback): self;

    /**
     * Check if the model can be deleted when locked.
     *
     * @return bool
     */
    public function canDeleteWhenLocked(): bool;

    /**
     * Check if the model can be restored while locked.
     *
     * @return bool
     */
    public function canRestoreWhileLocked(): bool;

    /**
     * Check if the model can be saved while locked.
     *
     * @return bool
     */
    public function canSaveWhileLocked(): bool;

    /**
     * Get the column name used for locking the model.
     *
     * @return string
     */
    public function getLockColumn(): string;

    /**
     * Get the exceptions that should not trigger a lock exception.
     *
     * @return array
     */
    public function getLockExceptions(): array;
}
