<?php

namespace TestMonitor\Lockable\Exceptions;

use RuntimeException;

class ModelLockedException extends RuntimeException
{
    /**
     * The affected lockable Eloquent model.
     *
     * @var \TestMonitor\Lockable\Contracts\IsLockable
     */
    protected $model;

    /**
     * Set the affected Eloquent model.
     *
     * @param \TestMonitor\Lockable\Contracts\IsLockable $model
     * @return static
     */
    public function setModel($model): static
    {
        $this->model = $model;

        $modelName = get_class($model);

        $this->message = "[{$modelName}] is locked and cannot be modified or deleted ({$model->getKey()})";

        return $this;
    }

    /**
     * Get the affected Eloquent model.
     *
     * @return null|\TestMonitor\Lockable\Contracts\IsLockable
     */
    public function getModel()
    {
        return $this->model;
    }
}
