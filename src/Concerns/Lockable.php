<?php

namespace TestMonitor\Lockable\Concerns;

use TestMonitor\Lockable\Contracts\IsLockable;
use TestMonitor\Lockable\Exceptions\ModelLockedException;

trait Lockable
{
    public static function bootLockable()
    {
        static::saving(function (IsLockable $model) {
            if ($model->exists && $model->isLocked() && !($model->isLocking() || $model->isUnlocking())) {
                throw (new ModelLockedException)->setModel($model);
            }
        });

        static::deleting(function (IsLockable $model) {
            if ($model->isLocked()) {
                throw (new ModelLockedException)->setModel($model);
            }
        });
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

    public function lock(): self
    {
        $this->{$this->getLockColumn()} = true;
        $this->save();

        return $this;
    }

    public function unlock(): self
    {
        $this->{$this->getLockColumn()} = false;
        $this->save();

        return $this;
    }

    public function whileLocked(callable $callback): self
    {
        $this->lock();

        try {
            $callback($this);
        } finally {
            $this->unlock();
        }

        return $this;
    }

    public function whileUnlocked(callable $callback): self
    {
        $this->unlock();

        try {
            $callback($this);
        } finally {
            $this->lock();
        }

        return $this;
    }
}
