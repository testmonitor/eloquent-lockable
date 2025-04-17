<?php

namespace TestMonitor\Lockable\Test;

use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Lockable\Test\Models\User;
use TestMonitor\Lockable\Exceptions\ModelLockedException;

class LockableTest extends TestCase
{
    #[Test]
    public function it_allows_updating_when_not_locked()
    {
        $model = User::create(['name' => 'Update Me', 'locked' => false]);
        $model->name = 'Change Me';
        $model->save();

        $this->assertDatabaseHas('users', ['name' => 'Change Me']);
    }

    #[Test]
    public function it_allows_deleting_when_not_locked()
    {
        $model = User::create(['name' => 'Delete Me', 'locked' => false]);
        $model->delete();

        $this->assertDatabaseMissing('users', ['id' => $model->id]);
    }

    #[Test]
    public function it_blocks_updating_when_locked()
    {
        $model = User::create(['name' => 'Lock Me', 'locked' => true]);

        $this->expectException(ModelLockedException::class);

        $model->name = 'Try Change';
        $model->save();
    }


    #[Test]
    public function it_sets_model_on_locked_exception_when_updating()
    {
        $model = User::create(['name' => 'Lock Me', 'locked' => true]);

        try {
            $model->name = 'Try Change';
            $model->save();
            $this->fail('ModelLockedException was not thrown.');
        } catch (ModelLockedException $e) {
            $this->assertInstanceOf(User::class, $e->getModel());
            $this->assertEquals($model->id, $e->getModel()->id);
        }
    }

    #[Test]
    public function it_blocks_deleting_when_locked()
    {
        $model = User::create(['name' => 'Lock Me', 'locked' => true]);

        $this->expectException(ModelLockedException::class);

        $model->delete();
    }

    #[Test]
    public function it_sets_model_on_locked_exception_when_deleting()
    {
        $model = User::create(['name' => 'Lock Me', 'locked' => true]);

        try {
            $model->delete();
            $this->fail('ModelLockedException was not thrown.');
        } catch (ModelLockedException $e) {
            $this->assertInstanceOf(User::class, $e->getModel());
            $this->assertEquals($model->id, $e->getModel()->id);
        }
    }

    #[Test]
    public function it_can_lock_and_unlock_a_model()
    {
        $model = User::create(['name' => 'Lock and Unlock Me', 'locked' => false]);

        $model->lock();
        $this->assertTrue($model->fresh()->isLocked());

        $model->unlock();
        $this->assertFalse($model->fresh()->isLocked());
    }

    #[Test]
    public function it_runs_callback_while_locked_and_then_unlocks()
    {
        $model = User::create(['name' => 'Unlocked', 'locked' => false]);

        $model->whileLocked(function ($m) {
            $this->assertTrue($m->isLocked());
        });

        $this->assertEquals('Unlocked', $model->fresh()->name);
        $this->assertFalse($model->fresh()->isLocked());
    }

    #[Test]
    public function it_runs_callback_while_unlocked_and_then_relocks()
    {
        $model = User::create(['name' => 'Locked', 'locked' => true]);

        $model->whileUnlocked(function ($m) {
            $this->assertFalse($m->isLocked());
            $m->name = 'Temporarily Unlocked';
            $m->save();
        });

        $this->assertEquals('Temporarily Unlocked', $model->fresh()->name);
        $this->assertTrue($model->fresh()->isLocked());
    }
}
