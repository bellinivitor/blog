<?php

namespace Domain\Post\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
