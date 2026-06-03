<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneReadNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_prunes_read_notifications_older_than_retention(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ORDER_PLACED,
            'title' => 'Old read',
            'message' => 'Should be deleted',
            'read_at' => now()->subDays(11),
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ORDER_PLACED,
            'title' => 'Recent read',
            'message' => 'Should remain',
            'read_at' => now()->subDays(5),
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ORDER_PLACED,
            'title' => 'Unread',
            'message' => 'Should remain',
            'read_at' => null,
        ]);

        $this->artisan('notifications:prune-read', ['--days' => 10])
            ->assertSuccessful();

        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseMissing('notifications', ['title' => 'Old read']);
        $this->assertDatabaseHas('notifications', ['title' => 'Recent read']);
        $this->assertDatabaseHas('notifications', ['title' => 'Unread']);
    }

    public function test_prune_skipped_when_days_zero(): void
    {
        config(['notifications.read_retention_days' => 0]);

        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ORDER_PLACED,
            'title' => 'Old read',
            'message' => 'Keep',
            'read_at' => now()->subDays(30),
        ]);

        $this->artisan('notifications:prune-read')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);
    }
}
