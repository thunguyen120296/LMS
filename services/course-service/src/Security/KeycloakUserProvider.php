<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class KeycloakUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if ($identifier === '') {
            throw new UserNotFoundException('Empty user identifier.');
        }

        return new KeycloakUser($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof KeycloakUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === KeycloakUser::class;
    }
}
