<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class KeycloakUser implements UserInterface
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        private readonly string $identifier,
        private readonly array $roles = [],
    ) {}

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Đảm bảo luôn có role mặc định của Symfony
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }
}