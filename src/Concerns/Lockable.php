<?php

namespace TestMonitor\Lockable\Concerns;

use TestMonitor\Lockable\Contracts\IsLockable;
use TestMonitor\Lockable\Exceptions\ModelLockedException;

trait Lockable
{
    public static function bootLockable()
    {
        static::saving(function (IsLockable $model) {
            if ($model->exists && $model->isLocked() && ! ($model->isLocking() || $model->isUnlocking())) {
                throw (new ModelLockedException)->setModel($model);
            }
        });

        static::deleting(function (IsLockable $model) {
            if ($model->isLocked() && !$model->canDeleteWhenLocked()) {
                throw (new ModelLockedException)->setModel($model);
            }
        });
    }

    public function canDeleteWhenLocked(): bool
    {
        return false;
    }

    public function getLockColumn(): string
    {
        return 'locked';
    }

    public function isLocked(): bool
    {
        return (bool) $this->{$this->getLockColumn()};
    }

    public function isUnlocked(): bool
    {
        return ! $this->isLocked();
    }

    protected function islocking(): bool
    {
        return $this->isDirty($this->getLockColumn()) && $this->getAttribute($this->getLockColumn()) === true;
    }

    protected function isUnlocking(): bool
    {
        return $this->isDirty($this->getLockColumn()) && $this->getAttribute($this->getLockColumn()) === false;
    }

    public function setLocked(bool $state = true): self
    {
        $this->setAttribute($this->getLockColumn(), $state);

        return $this;
    }

    public function setUnlocked(): self
    {
        return $this->setLocked(false);
    }

    public function markLocked(): self
    {
        $this->setLocked(true)->save();

        return $this;
    }

    public function markUnlocked(): self
    {
        $this->setUnlocked()->save();

        return $this;
    }

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
}
