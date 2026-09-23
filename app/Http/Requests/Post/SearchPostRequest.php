<?php

namespace App\Http\Requests\Post;

use Domain\Post\Enums\PostStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(PostStatus::class)],
            'tag_id' => ['nullable', 'integer'],
            'trashed' => ['nullable', 'boolean'],
        ];
    }
}
