<?php

declare(strict_types=1);

namespace App\Message;

final readonly class UserVerifiedMessage
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $ssoSubject,
        public string $occurredAt,
    ) {}
}
