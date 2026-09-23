<?php

namespace Domain\Post\Exceptions;

use Domain\Shared\Exceptions\DomainHttpException;
use Symfony\Component\HttpFoundation\Response;

class PostImageUploadFailedException extends DomainHttpException
{
    public function __construct()
    {
        parent::__construct(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            'The image could not be stored.',
        );
    }
}
