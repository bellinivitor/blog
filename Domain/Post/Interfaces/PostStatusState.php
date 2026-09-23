<?php

namespace Domain\Post\Interfaces;

use Domain\Post\Enums\PostStatus;

interface PostStatusState
{
    public function handle(): PostStatus;
}
