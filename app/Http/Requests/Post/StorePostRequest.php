<?php

namespace App\Http\Requests\Post;

use Domain\Post\Actions\ListReservedSlugsAction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StorePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::notIn(app(ListReservedSlugsAction::class)()), $this->uniqueSlugRule()],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', Rule::exists('tags', 'id')->withoutTrashed()],
            'new_tags' => ['nullable', 'array'],
            'new_tags.*' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'tag_ids.*.exists' => 'The selected tag does not exist.',
            'slug.not_in' => 'This slug is reserved by the blog. Choose another one.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'new_tags.*' => 'tag name',
        ];
    }

    protected function uniqueSlugRule(): Unique
    {
        return Rule::unique('posts', 'slug');
    }
}
