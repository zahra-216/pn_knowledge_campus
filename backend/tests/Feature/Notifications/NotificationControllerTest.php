<?php

namespace Tests\Feature\Notifications;

use App\Models\Application;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function application(): Application
    {
        return Application::create(['first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'jane@example.com', 'status' => 'submitted', 'submitted_at' => now()]);
    }

    public function test_a_user_sees_their_own_notifications_and_unread_count(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $admissions->notify(new NewApplicationNotification($this->application()));

        $response = $this->actingAs($admissions)->getJson('/api/v1/admin/notifications');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.items'));
        $this->assertSame(1, $response->json('data.unread_count'));
        $this->assertSame('New Application Submitted', $response->json('data.items.0.data.title'));
    }

    public function test_a_user_never_sees_another_users_notifications(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $otherAdmissions = $this->userWithRole('Admissions');
        $otherAdmissions->notify(new NewApplicationNotification($this->application()));

        $response = $this->actingAs($admissions)->getJson('/api/v1/admin/notifications');

        $response->assertOk();
        $this->assertCount(0, $response->json('data.items'));
    }

    public function test_marking_a_notification_read_updates_read_at_and_unread_count(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $admissions->notify(new NewApplicationNotification($this->application()));
        $notificationId = $admissions->notifications()->first()->id;

        $response = $this->actingAs($admissions)->patchJson("/api/v1/admin/notifications/{$notificationId}/read");
        $response->assertOk();
        $this->assertNotNull($response->json('data.read_at'));

        $this->assertSame(0, $this->actingAs($admissions)->getJson('/api/v1/admin/notifications')->json('data.unread_count'));
    }

    public function test_cannot_mark_another_users_notification_as_read(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $otherAdmissions = $this->userWithRole('Admissions');
        $otherAdmissions->notify(new NewApplicationNotification($this->application()));
        $notificationId = $otherAdmissions->notifications()->first()->id;

        $this->actingAs($admissions)->patchJson("/api/v1/admin/notifications/{$notificationId}/read")->assertNotFound();
    }

    public function test_mark_all_read_clears_unread_count(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $admissions->notify(new NewApplicationNotification($this->application()));
        $admissions->notify(new NewApplicationNotification($this->application()));

        $this->actingAs($admissions)->patchJson('/api/v1/admin/notifications/read-all')->assertNoContent();

        $this->assertSame(0, $this->actingAs($admissions)->getJson('/api/v1/admin/notifications')->json('data.unread_count'));
    }
}
