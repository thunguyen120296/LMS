<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Lms\Shared\Exception\ApiException;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegisterService
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly UserRepository $userRepository,
        private readonly string $keycloakUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('register');
    }

    /**
     * @param array{email: string, password: string, fullName?: string} $payload
     */
    public function createUser(array $payload): User
    {
        $email = $payload['email'];

        $this->logger->info('Starting user registration', new LogContext(
            action: 'register.create_user',
            extra: ['email' => $email],
        ));

        if ($this->userRepository->findByEmail($email)) {
            $this->logger->warning('Registration failed: email already exists in local database', new LogContext(
                action: 'register.create_user',
                extra: ['email' => $email],
            ));

            throw new ApiException('Email đã được sử dụng', 409, [
                ['field' => 'email', 'message' => 'Email đã được sử dụng'],
            ]);
        }

        // 1. Giao tiếp với Keycloak để tạo User và gán Role bên SSO
        $adminToken = $this->getAdminToken();
        $ssoSubject = $this->createKeycloakUser($adminToken, $payload);
        $this->assignDefaultKeycloakRoles($adminToken, $ssoSubject);

        // 2. Chỉ lưu thông tin User tối thiểu kèm ssoSubject xuống DB cục bộ
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setSsoProvider('keycloak');
        $user->setSsoSubject($ssoSubject);

        if (!empty($payload['fullName'])) {
            $nameParts = explode(' ', trim($payload['fullName']), 2);
            $user->setFirstName($nameParts[0]);
            $user->setLastName($nameParts[1] ?? null);
        }

        $this->userRepository->save($user, true);

        $this->logger->info('User registered successfully', new LogContext(
            action: 'register.create_user',
            userId: $user->getId(),
            extra: [
                'email' => $user->getEmail(),
                'ssoSubject' => $ssoSubject,
            ],
        ));

        return $user;
    }

    private function getAdminToken(): string
    {
        $this->logger->info('Requesting Keycloak admin token', new LogContext(
            action: 'register.get_admin_token',
        ));

        try {
            $response = $this->client->request('POST', $this->keycloakUrl . '/realms/lms/protocol/openid-connect/token', [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => http_build_query([
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]),
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Failed to obtain Keycloak admin token', null, new LogContext(
                    action: 'register.get_admin_token',
                    extra: ['statusCode' => $response->getStatusCode()],
                ));

                throw new ApiException('Không thể kết nối dịch vụ xác thực', 500);
            }

            $this->logger->info('Keycloak admin token obtained', new LogContext(
                action: 'register.get_admin_token',
            ));

            return $response->toArray()['access_token'];
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Keycloak admin token request failed', $e, new LogContext(
                action: 'register.get_admin_token',
            ));

            throw new ApiException('Không thể kết nối dịch vụ xác thực', 500);
        }
    }

    /**
     * @param array{email: string, password: string, fullName?: string} $payload
     */
    private function createKeycloakUser(string $adminToken, array $payload): string
    {
        $this->logger->info('Creating user in Keycloak', new LogContext(
            action: 'register.create_keycloak_user',
            extra: ['email' => $payload['email']],
        ));

        try {
            $response = $this->client->request('POST', $this->keycloakUrl . '/admin/realms/lms/users', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                ],
                'json' => [
                    'username' => trim($payload['email']),
                    'email' => trim($payload['email']),
                    'enabled' => true,
                    'emailVerified' => false,
                    'firstName' => $this->extractFirstName($payload['fullName'] ?? null),
                    'lastName' => $this->extractLastName($payload['fullName'] ?? null),
                    'credentials' => [
                        [
                            'type' => 'password',
                            'value' => $payload['password'],
                            'temporary' => false,
                        ],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 409) {
                $this->logger->warning('Keycloak rejected registration: email already exists', new LogContext(
                    action: 'register.create_keycloak_user',
                    extra: ['email' => $payload['email'], 'statusCode' => $statusCode],
                ));

                throw new ApiException('Email đã được sử dụng', 409, [
                    ['field' => 'email', 'message' => 'Email đã được sử dụng'],
                ]);
            }

            if ($statusCode !== 201) {
                $errorResponse = $response->getContent(false);
                $this->logger->error('Keycloak user creation failed', null, new LogContext(
                    action: 'register.create_keycloak_user',
                    extra: ['email' => $payload['email'], 'statusCode' => $statusCode, 'keycloak_error' => $errorResponse],
                ));

                throw new ApiException('Tạo người dùng thất bại từ Keycloak: ' . $errorResponse, 500);
            }

            $location = $response->getHeaders()['location'][0] ?? null;
            if (!$location || !preg_match('#/users/([^/]+)$#', $location, $matches)) {
                $this->logger->error('Keycloak user created but subject id could not be resolved', null, new LogContext(
                    action: 'register.create_keycloak_user',
                    extra: ['email' => $payload['email'], 'location' => $location],
                ));

                throw new ApiException('Tạo người dùng thất bại', 500);
            }

            return $matches[1];
        } catch (ClientExceptionInterface $e) {
            $rawResponse = $e->getResponse()->getContent(false);
            if ($e->getResponse()->getStatusCode() === 409) {
                throw new ApiException('Email đã được sử dụng', 409, [
                    ['field' => 'email', 'message' => 'Email đã được sử dụng'],
                ]);
            }

            throw new ApiException('Tạo người dùng thất bại: ' . $rawResponse, 500);
        } catch (TransportExceptionInterface $e) {
            throw new ApiException('Không thể kết nối dịch vụ xác thực', 500);
        }
    }

    private function assignDefaultKeycloakRoles(string $adminToken, string $userId): void
    {
        $this->assignRealmRole($adminToken, $userId, 'STUDENT');
    }

    private function assignRealmRole(string $adminToken, string $userId, string $roleName): void
    {
        $role = $this->fetchRealmRole($adminToken, $roleName);

        $this->client->request(
            'POST',
            $this->keycloakUrl . '/admin/realms/lms/users/' . $userId . '/role-mappings/realm',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $adminToken,
                ],
                'body' => json_encode([$role]),
            ],
        );
    }

    /** @return array<string, mixed> */
    private function fetchRealmRole(string $adminToken, string $roleName): array
    {
        $response = $this->client->request(
            'GET',
            $this->keycloakUrl . '/admin/realms/lms/roles/' . rawurlencode($roleName),
            [
                'headers' => ['Authorization' => 'Bearer ' . $adminToken],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new ApiException('Role Keycloak chưa được cấu hình: ' . $roleName, 500);
        }

        $role = $response->toArray();

        return [
            'id' => $role['id'] ?? null,
            'name' => $role['name'] ?? null,
        ];
    }

    private function extractFirstName(?string $fullName): ?string
    {
        if ($fullName === null || trim($fullName) === '') {
            return null;
        }

        return explode(' ', trim($fullName), 2)[0];
    }

    private function extractLastName(?string $fullName): ?string
    {
        if ($fullName === null || trim($fullName) === '') {
            return null;
        }

        $parts = explode(' ', trim($fullName), 2);

        return $parts[1] ?? null;
    }
}