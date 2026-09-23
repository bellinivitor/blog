<?php

namespace Domain\Post\States;

use Domain\Post\Enums\PostStatus;
use Domain\Post\Exceptions\InvalidPostStatusTransitionException;
use Domain\Post\Interfaces\PostStatusState;

readonly class PublishedState implements PostStatusState
{
    public function __construct(
        private PostStatus $currentStatus,
    ) {}

    public function handle(): PostStatus
    {
        if ($this->currentStatus === PostStatus::Published) {
            throw new InvalidPostStatusTransitionException($this->currentStatus);
        }

        return PostStatus::Published;
    }
}
