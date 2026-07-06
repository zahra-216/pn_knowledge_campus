<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SRS FR-05/FR-26 — "log every Inquiry (contact form, course inquiry,
 * downloads) into an Inquiry Management module with status tracking
 * (New, In Progress, Resolved, Spam)." This is a deliberately minimal
 * slice: the public capture endpoint the Contact page and Course
 * Detail's "Enquire Now" flow need to exist at all, added as part of
 * the Public Website milestone since those forms have nowhere to submit
 * to otherwise. The full CMS Inquiry Management inbox (assignment,
 * internal notes, in-app/email notifications per FR-32) is correctly
 * its own later Roadmap Stage 6 milestone and isn't built here — this
 * table only needs to support the write side and a plain status column
 * for now.
 *
 * Not in the doc's soft-delete blanket list (Section 2.1 names content
 * tables specifically; Inquiries is a submission log, not curated
 * content), so no `deleted_at`. No audit columns either — every row is
 * created by an anonymous public visitor, and there's no admin write
 * path yet to audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 255);
            $table->string('phone', 30)->nullable();
            $table->text('message');
            $table->string('source_page', 255)->nullable();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->boolean('international_applicant')->default(false);
            $table->enum('status', ['new', 'in_progress', 'resolved', 'spam'])->default('new');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
