<?php

declare(strict_types=1);

namespace App\Message;

final readonly class UserRegisteredMessage
{
    public function __construct(
        public string $userId,
        public string $email,
        public ?string $fullName,
        public string $occurredAt,
    ) {}
}
