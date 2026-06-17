<?php

declare(strict_types=1);

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class KeycloakClaimsReader
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly string $clientId,
    ) {}

    /** @return array<string, mixed> */
    public function getPayload(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return [];
        }

        $token = $request->cookies->get('auth_token');
        if (!is_string($token) || $token === '') {
            return [];
        }

        try {
            return $this->jwtManager->parse($token);
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<string> */
    public function getClientRoles(?string $clientId = null): array
    {
        $payload = $this->getPayload();
        $clientId ??= $this->clientId;
        $roles = $payload['resource_access'][$clientId]['roles'] ?? [];

        if (!is_array($roles)) {
            return [];
        }

        return array_values(array_unique(array_filter($roles, 'is_string')));
    }
}
