<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\KeycloakClaimsReader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PermissionVoter extends Voter
{
    public function __construct(
        private readonly KeycloakClaimsReader $claimsReader,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_contains($attribute, ':');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($token->getUser() === null) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        return in_array($attribute, $this->claimsReader->getClientRoles(), true);
    }
}
