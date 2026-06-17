<?php

// src/Security/UserProvider.php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $userRepository) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findWithRolesAndPermissionsByEmail($identifier);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        // Không cần refresh vì chúng ta không lưu session
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }
}