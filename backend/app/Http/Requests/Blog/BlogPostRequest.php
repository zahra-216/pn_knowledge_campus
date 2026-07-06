<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/blog. `featured_image_media_id`/`gallery_media_ids`
 * reference already-uploaded Media Library items (Course's pattern);
 * `tags` accepts an array of tag names — the controller resolves each to
 * a Tag row via firstOrCreate, so admins can type a new tag inline
 * instead of pre-creating it on the separate /admin/tags screen first.
 * `author_id` defaults to the current user in the controller when
 * omitted on create; the field is exposed here so an Administrator can
 * reassign authorship without needing a full Users admin screen (not
 * built yet — Roadmap Stage 7).
 */
class BlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by blog.create/edit in the controller
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('title')) {
            $this->merge(['slug' => Str::slug($this->input('title'))]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $postId = $this->route('blogPost')?->id;

        return [
            'category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'slug' => [
                $isCreate ? 'required' : 'sometimes', 'string', 'max:220', 'alpha_dash',
                Rule::unique('blog_posts', 'slug')->ignore($postId),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['sometimes', 'boolean'],

            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:60'],

            'seo' => ['nullable', 'array'],
            'seo.seo_title' => ['nullable', 'string', 'max:160'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
            'seo.keywords' => ['nullable', 'string', 'max:255'],
            'seo.canonical_url' => ['nullable', 'string', 'max:255'],
            'seo.schema_type' => ['nullable', 'string', 'max:60'],
            'seo.og_title' => ['nullable', 'string', 'max:160'],
            'seo.og_description' => ['nullable', 'string', 'max:320'],
        ];
    }
}
