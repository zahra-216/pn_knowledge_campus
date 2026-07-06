<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 20 (Online Applications) — a visitor-facing admissions
 * application, distinct from the minimal `inquiries` capture slice
 * (Milestone 19's Contact Module). There is no visitor authentication
 * system anywhere in this project, so a submitted/in-progress
 * application is looked up and modified using `application_number`
 * (the public reference, never the internal `id`) plus `email` as a
 * matching pair — the same "order number + email" pattern real
 * admissions/e-commerce systems use when they don't want to force an
 * account signup. See ApplicationController's docblock for the actual
 * authorization check this enables.
 *
 * `status` models the review lifecycle a visitor and staff both walk
 * through: draft (visitor still editing, not visible to staff) ->
 * submitted (visitor is done, awaiting staff) -> under_review (staff
 * picked it up) -> approved | rejected (final).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 20)->unique();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255);
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->text('address')->nullable();
            $table->boolean('international_applicant')->default(false);
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
