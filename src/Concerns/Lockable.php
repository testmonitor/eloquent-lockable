<?php

namespace TestMonitor\Lockable\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use TestMonitor\Lockable\Contracts\IsLockable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use TestMonitor\Lockable\Exceptions\ModelLockedException;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Lockable
{
    /**
     * Boot the lockable trait for the model.
     */
    public static function bootLockable()
    {
        static::saving(function (IsLockable $model) {
            if ($model->exists && $model->isLocked() && ! $model->canSaveWhileLocked()) {
                throw (new ModelLockedException)->setModel($model);
            }
        });

        static::deleting(function (IsLockable $model) {
            if ($model->isLocked() && ! $model->canDeleteWhenLocked()) {
                throw (new ModelLockedException)->setModel($model);
            }
        });
    }

    /**
     * Check if the model can be deleted when locked.
     *
     * @return bool
     */
    public function canDeleteWhenLocked(): bool
    {
        return false;
    }

    /**
     * Check if the model can be restored while locked.
     *
     * @return bool
     */
    public function canRestoreWhileLocked(): bool
    {
        return $this->canDeleteWhenLocked();
    }

    /**
     * Check if the model can be saved while locked.
     *
     * @return bool
     */
    public function canSaveWhileLocked(): bool
    {
        return empty($this->dirtyWithoutLockExceptions()) ||
            $this->isLocking() ||
            $this->isUnlocking() ||
            ($this->isRestoringWhileLocked() && $this->canRestoreWhileLocked());
    }

    /**
     * Get the dirty attributes of the model excluding lock exceptions.
     *
     * @return array
     */
    protected function dirtyWithoutLockExceptions(): array
    {
        $dirty = $this->getDirty();
        $exceptions = array_flip($this->getLockExceptions());

        return array_diff_key($dirty, $exceptions);
    }

    /**
     * Get the column name used for locking the model.
     *
     * @return string
     */
    public function getLockColumn(): string
    {
        return 'locked';
    }

    /**
     * Get the exceptions that should not trigger a lock exception.
     *
     * @return array
     */
    public function getLockExceptions(): array
    {
        return [];
    }

    /**
     * Check if the model is currently locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return (bool) $this->{$this->getLockColumn()};
    }

    /**
     * Check if the model is currently unlocked.
     *
     * @return bool
     */
    public function isUnlocked(): bool
    {
        return ! $this->isLocked();
    }

    /**
     * Check if the model is currently being locked.
     *
     * @return bool
     */
    protected function isLocking(): bool
    {
        return $this->isDirty($this->getLockColumn()) && $this->getAttribute($this->getLockColumn()) === true;
    }

    /**
     * Check if the model is currently being unlocked.
     *
     * @return bool
     */
    protected function isUnlocking(): bool
    {
        return $this->isDirty($this->getLockColumn()) && $this->getAttribute($this->getLockColumn()) === false;
    }

    /**
     * Check if the model is both lockable and soft deletable.
     *
     * @return bool
     */
    protected function isLockableAndSoftDeletable(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(get_class($this))) &&
            method_exists($this, 'getDeletedAtColumn');
    }

    /**
     * Check if the model is restoring while being locked.
     *
     * @return bool
     */
    protected function isRestoringWhileLocked(): bool
    {
        return $this->isLocked() &&
            $this->isLockableAndSoftDeletable() &&
            $this->getDirty() === [$this->getDeletedAtColumn() => null];
    }

    /**
     * Set the lock state of the model.
     *
     * @param bool $state
     * @return self
     */
    public function setLocked(bool $state = true): self
    {
        $this->setAttribute($this->getLockColumn(), $state);

        return $this;
    }

    /**
     * Set the model to an unlocked state.
     *
     * @return self
     */
    public function setUnlocked(): self
    {
        return $this->setLocked(false);
    }

    /**
     * Mark the model as locked and save the state.
     *
     * @return self
     */
    public function markLocked(): self
    {
        $this->setLocked(true)->save();

        return $this;
    }

    /**
     * Mark the model as unlocked and save the state.
     *
     * @return self
     */
    public function markUnlocked(): self
    {
        $this->setUnlocked()->save();

        return $this;
    }

    /**
     * Execute a callback while the model is locked.
     *
     * @param callable $callback
     * @return self
     */
    public function whileLocked(callable $callback): self
    {
        $this->markLocked();

        try {
            $callback($this);
        } finally {
            $this->markUnlocked();
        }

        return $this;
    }

    /**
     * Execute a callback while the model is unlocked.
     *
     * @param callable $callback
     * @return self
     */
    public function whileUnlocked(callable $callback): self
    {
        $this->markUnlocked();

        try {
            $callback($this);
        } finally {
            $this->markLocked();
        }

        return $this;
    }

    /**
     * Scope a query to only include locked models.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $query
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function scopeLocked(Builder $query): Builder
    {
        return $query->where($this->getLockColumn(), true);
    }

    /**
     * Scope a query to only include unlocked models.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $query
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where($this->getLockColumn(), false);
    }
}
