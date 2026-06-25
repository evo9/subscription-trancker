<?php

declare(strict_types=1);

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\RenewalReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('GET /api/notifications', function (): void {
    it('returns all notifications for the authenticated user', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $user->notify(new RenewalReminder(
            Subscription::factory()->for($user)->create(),
        ));

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data' => [['id', 'data', 'read_at', 'created_at']]]);
    });

    it('does not include another user notifications', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $other->notify(new RenewalReminder(
            Subscription::factory()->for($other)->create(),
        ));

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('requires authentication', function (): void {
        $this->getJson('/api/notifications')->assertUnauthorized();
    });
});

describe('POST /api/notifications/{id}/read', function (): void {
    it('marks a notification as read', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $user->notify(new RenewalReminder(
            Subscription::factory()->for($user)->create(),
        ));

        $notification = $user->notifications()->first();
        expect($notification->read_at)->toBeNull();

        $this->postJson("/api/notifications/{$notification->id}/read")
            ->assertNoContent();

        expect($notification->fresh()->read_at)->not->toBeNull();
    });

    it('returns 404 for another user notification', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $other->notify(new RenewalReminder(
            Subscription::factory()->for($other)->create(),
        ));

        $othersNotification = $other->notifications()->first();

        $this->postJson("/api/notifications/{$othersNotification->id}/read")
            ->assertNotFound();
    });

    it('requires authentication', function (): void {
        $this->postJson('/api/notifications/fake-id/read')->assertUnauthorized();
    });
});
