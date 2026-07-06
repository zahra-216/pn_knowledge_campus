<?php

namespace App\Http\Requests\SocialLinks;

use Illuminate\Foundation\Http\FormRequest;

class SocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by settings.edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'platform' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:50'],
            'url' => [$isCreate ? 'required' : 'sometimes', 'url', 'max:255'],
            'order' => ['integer'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }
    }
}
