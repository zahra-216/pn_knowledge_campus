<?php

namespace Tests\Feature\Inquiries;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Setting;
use App\Notifications\InquiryConfirmationNotification;
use App\Notifications\NewInquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_submit_an_inquiry_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/inquiries', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1234567890',
            'message' => 'I would like more information about your programs.',
            'source_page' => '/contact',
        ]);

        $response->assertCreated();
        $this->assertSame('new', $response->json('data.status'));
        $this->assertDatabaseHas('inquiries', ['email' => 'jane@example.com', 'status' => 'new']);
    }

    public function test_submitting_an_inquiry_notifies_staff_and_the_visitor(): void
    {
        Notification::fake();
        Setting::create(['key' => 'admissions_email', 'value' => 'admissions@example.com', 'group' => 'contact', 'is_public' => true]);

        $this->postJson('/api/v1/inquiries', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'I would like more information about your programs.',
        ])->assertCreated();

        Notification::assertSentOnDemand(
            NewInquiryNotification::class,
            fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'admissions@example.com'
        );
        Notification::assertSentOnDemand(
            InquiryConfirmationNotification::class,
            fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'jane@example.com'
        );
    }

    public function test_a_mail_delivery_failure_does_not_fail_the_submission(): void
    {
        // 127.0.0.1 with an unused port fails fast (connection refused)
        // rather than a slow DNS-lookup timeout an unresolvable hostname
        // would cause, keeping this test quick.
        Setting::create(['key' => 'smtp_host', 'value' => '127.0.0.1', 'group' => 'smtp', 'is_public' => false]);
        Setting::create(['key' => 'smtp_port', 'value' => '1', 'group' => 'smtp', 'is_public' => false]);

        $this->postJson('/api/v1/inquiries', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Message',
        ])->assertCreated();

        $this->assertDatabaseHas('inquiries', ['email' => 'jane@example.com']);
    }

    public function test_name_email_and_message_are_required(): void
    {
        $this->postJson('/api/v1/inquiries', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_can_be_linked_to_a_course_with_the_international_applicant_flag(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
        ]);

        $response = $this->postJson('/api/v1/inquiries', [
            'name' => 'International Applicant',
            'email' => 'applicant@example.com',
            'message' => 'I am applying from abroad.',
            'course_id' => $course->id,
            'international_applicant' => true,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('inquiries', [
            'email' => 'applicant@example.com',
            'course_id' => $course->id,
            'international_applicant' => true,
        ]);
    }

    public function test_course_id_must_reference_a_real_course(): void
    {
        $this->postJson('/api/v1/inquiries', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Message',
            'course_id' => 999999,
        ])->assertUnprocessable()->assertJsonValidationErrors(['course_id']);
    }
}
