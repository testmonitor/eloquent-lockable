<?php

namespace TestMonitor\Lockable\Test;

use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function setUpDatabase(Application $app)
    {
        $builder = $this->app['db']->connection()->getSchemaBuilder();

        $builder->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
