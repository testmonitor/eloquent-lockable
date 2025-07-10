<?php

namespace TestMonitor\Lockable\Test;

use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Lockable\Test\Models\User;
use Illuminate\Database\Eloquent\Collection;
use TestMonitor\Lockable\Test\Models\SoftDeletableUser;
use TestMonitor\Lockable\Exceptions\ModelLockedException;
use TestMonitor\Lockable\Test\Models\UserWithLockExceptions;
use TestMonitor\Lockable\Test\Models\DeletableWhenLockedUser;
use TestMonitor\Lockable\Test\Models\SoftDeletableWhenLockedUser;

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
    public function it_blocks_soft_deleting_when_locked()
    {
        $model = SoftDeletableUser::create(['name' => 'Lock Me Softly', 'locked' => true]);

        $this->expectException(ModelLockedException::class);

        $model->delete();
    }

    #[Test]
    public function it_allows_deleting_when_locked_but_deletion_is_allowed()
    {
        $model = DeletableWhenLockedUser::create(['name' => 'Delete Me', 'locked' => true]);

        $model->delete();

        $this->assertDatabaseMissing('users', ['id' => $model->id]);
    }

    #[Test]
    public function it_allows_soft_deleting_when_locked_but_deletion_is_allowed()
    {
        $model = SoftDeletableWhenLockedUser::create(['name' => 'Delete Me Softly', 'locked' => true]);

        $model->delete();

        $this->assertSoftDeleted($model);
    }

    #[Test]
    public function it_allows_restoring_when_locked_but_deletion_is_allowed()
    {
        $model = SoftDeletableWhenLockedUser::create(['name' => 'Restore Me Again', 'locked' => true]);
        $model->delete();

        $model->restore();

        $this->assertNotSoftDeleted($model);
    }

    #[Test]
    public function it_allows_saving_when_locked_but_attribute_is_on_exception_list()
    {
        $model = UserWithLockExceptions::create(['name' => 'Change Me', 'locked' => true]);

        $model->note = 'Some note';
        $model->save();

        $this->assertEquals('Some note', $model->note);
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

        $model->markLocked();
        $this->assertTrue($model->fresh()->isLocked());

        $model->markUnlocked();
        $this->assertTrue($model->fresh()->isUnlocked());
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

    #[Test]
    public function it_can_filter_out_locked_models_using_the_locked_scope()
    {
        User::create(['name' => 'Locked #1', 'locked' => true]);
        User::create(['name' => 'Locked #2', 'locked' => true]);
        User::create(['name' => 'Unlocked #1', 'locked' => false]);

        $results = User::query()->locked()->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(2, $results->count());
        $this->assertEquals('Locked #1', $results->first()->name);
        $this->assertEquals('Locked #2', $results->last()->name);
    }

    #[Test]
    public function it_can_filter_out_unlocked_models_using_the_unlocked_scope()
    {
        User::create(['name' => 'Locked #1', 'locked' => true]);
        User::create(['name' => 'Locked #2', 'locked' => true]);
        User::create(['name' => 'Unlocked #1', 'locked' => false]);

        $results = User::query()->unlocked()->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertEquals('Unlocked #1', $results->first()->name);
    }
}
