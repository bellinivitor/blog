<?php

namespace App\Http\Requests\Post;

use App\Models\Post\Post;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdatePostRequest extends StorePostRequest
{
    protected function uniqueSlugRule(): Unique
    {
        /** @var Post $post */
        $post = $this->route('post');

        return Rule::unique('posts', 'slug')->ignore($post->id);
    }
}
