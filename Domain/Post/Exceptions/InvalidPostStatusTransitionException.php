<?php

namespace Domain\Post\Exceptions;

use Domain\Post\Enums\PostStatus;
use Domain\Shared\Exceptions\DomainHttpException;
use Symfony\Component\HttpFoundation\Response;

class InvalidPostStatusTransitionException extends DomainHttpException
{
    public function __construct(PostStatus $status)
    {
        parent::__construct(
            Response::HTTP_CONFLICT,
            "The post is already {$status->value}.",
        );
    }
}
