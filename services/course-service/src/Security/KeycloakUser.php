<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class KeycloakUser implements UserInterface
{
    public function __construct(
        private readonly string $identifier,
    ) {}

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }
}
