<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/blog/{id}/media — attach one or more
 * already-uploaded Media Library items to this post's gallery, mirroring
 * FacultyController::attachGallery's same pattern. Only one attachable
 * collection exists for Blog Post (gallery — featured_image is a
 * single-file replace via BlogPostRequest), so unlike CourseMediaRequest
 * there's no `collection` field to choose between.
 */
class BlogPostGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by blog.edit in the controller
    }

    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ];
    }
}
