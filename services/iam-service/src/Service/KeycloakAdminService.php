<?php

declare(strict_types=1);

namespace App\Service;

use Lms\Shared\Exception\ApiException;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KeycloakAdminService
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $keycloakUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('keycloak_admin');
    }

    public function getAdminToken(): string
    {
        $this->logger->info('Requesting Keycloak admin token', new LogContext(
            action: 'keycloak_admin.get_token',
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
                throw new ApiException('Không thể kết nối dịch vụ xác thực', 500);
            }

            return $response->toArray()['access_token'];
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Keycloak admin token request failed', $e, new LogContext(
                action: 'keycloak_admin.get_token',
            ));

            throw new ApiException('Không thể kết nối dịch vụ xác thực', 500);
        }
    }

    public function updateUserProfile(string $ssoSubject, ?string $firstName, ?string $lastName): void
    {
        $adminToken = $this->getAdminToken();

        $response = $this->client->request(
            'PUT',
            $this->keycloakUrl . '/admin/realms/lms/users/' . $ssoSubject,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'firstName' => $firstName ?? '',
                    'lastName' => $lastName ?? '',
                ],
            ],
        );

        if ($response->getStatusCode() !== 204) {
            $this->logger->error('Failed to update Keycloak user profile', null, new LogContext(
                action: 'keycloak_admin.update_user_profile',
                extra: ['ssoSubject' => $ssoSubject, 'statusCode' => $response->getStatusCode()],
            ));

            throw new ApiException('Không thể cập nhật hồ sơ trên hệ thống xác thực', 500);
        }
    }

    public function markEmailVerified(string $ssoSubject): void
    {
        $adminToken = $this->getAdminToken();

        $response = $this->client->request(
            'PUT',
            $this->keycloakUrl . '/admin/realms/lms/users/' . $ssoSubject,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'emailVerified' => true,
                    'requiredActions' => [],
                ],
            ],
        );

        if ($response->getStatusCode() !== 204) {
            $this->logger->error('Failed to update Keycloak emailVerified flag', null, new LogContext(
                action: 'keycloak_admin.mark_email_verified',
                extra: ['ssoSubject' => $ssoSubject, 'statusCode' => $response->getStatusCode()],
            ));

            throw new ApiException('Không thể cập nhật trạng thái xác minh email', 500);
        }
    }

    /**
     * @return array{id: string, email: string, enabled: bool}|null
     */
    public function findUserByEmail(string $email): ?array
    {
        $adminToken = $this->getAdminToken();

        try {
            $response = $this->client->request(
                'GET',
                $this->keycloakUrl . '/admin/realms/lms/users',
                [
                    'headers' => ['Authorization' => 'Bearer ' . $adminToken],
                    'query' => [
                        'email' => trim($email),
                        'exact' => 'true',
                    ],
                ],
            );

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $users = $response->toArray();

            if ($users === []) {
                return null;
            }

            $user = $users[0];

            return [
                'id' => (string) ($user['id'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'enabled' => (bool) ($user['enabled'] ?? false),
            ];
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to search Keycloak user by email', $e, new LogContext(
                action: 'keycloak_admin.find_user_by_email',
                extra: ['email' => $email],
            ));

            throw new ApiException('Không thể kết nối dịch vụ xác thực', 500);
        }
    }

    public function sendVerifyEmail(string $ssoSubject, string $redirectUri, int $lifespanSeconds = 86400): void
    {
        $this->sendExecuteActionsEmail($ssoSubject, ['VERIFY_EMAIL'], $redirectUri, $lifespanSeconds);
    }

    public function sendUpdatePasswordEmail(string $ssoSubject, string $redirectUri, int $lifespanSeconds = 3600): void
    {
        $this->sendExecuteActionsEmail($ssoSubject, ['UPDATE_PASSWORD'], $redirectUri, $lifespanSeconds);
    }

    /**
     * @param list<string> $actions
     */
    public function sendExecuteActionsEmail(
        string $ssoSubject,
        array $actions,
        string $redirectUri,
        int $lifespanSeconds = 3600,
    ): void {
        $adminToken = $this->getAdminToken();

        $response = $this->client->request(
            'PUT',
            $this->keycloakUrl . '/admin/realms/lms/users/' . $ssoSubject . '/execute-actions-email',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'client_id' => $this->clientId,
                    'redirect_uri' => $redirectUri,
                    'lifespan' => (string) $lifespanSeconds,
                ],
                'body' => json_encode($actions),
            ],
        );

        if ($response->getStatusCode() !== 204) {
            $this->logger->error('Failed to send Keycloak execute-actions email', null, new LogContext(
                action: 'keycloak_admin.send_execute_actions_email',
                extra: [
                    'ssoSubject' => $ssoSubject,
                    'actions' => $actions,
                    'statusCode' => $response->getStatusCode(),
                    'response' => $response->getContent(false),
                ],
            ));

            throw new ApiException('Không thể gửi email từ hệ thống xác thực', 500);
        }
    }

    /**
     * @return array{id: string, email: string, emailVerified: bool, enabled: bool}|null
     */
    public function getUserById(string $ssoSubject): ?array
    {
        $adminToken = $this->getAdminToken();

        try {
            $response = $this->client->request(
                'GET',
                $this->keycloakUrl . '/admin/realms/lms/users/' . $ssoSubject,
                [
                    'headers' => ['Authorization' => 'Bearer ' . $adminToken],
                ],
            );

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $user = $response->toArray();

            return [
                'id' => (string) ($user['id'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'emailVerified' => (bool) ($user['emailVerified'] ?? false),
                'enabled' => (bool) ($user['enabled'] ?? false),
            ];
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to fetch Keycloak user by id', $e, new LogContext(
                action: 'keycloak_admin.get_user_by_id',
                extra: ['ssoSubject' => $ssoSubject],
            ));

            throw new ApiException('Không thể kết nối dịch vụ xác thực', 500);
        }
    }

    public function resetPassword(string $ssoSubject, string $password): void
    {
        $adminToken = $this->getAdminToken();

        $response = $this->client->request(
            'PUT',
            $this->keycloakUrl . '/admin/realms/lms/users/' . $ssoSubject . '/reset-password',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'type' => 'password',
                    'value' => $password,
                    'temporary' => false,
                ],
            ],
        );

        if ($response->getStatusCode() !== 204) {
            $this->logger->error('Failed to reset Keycloak user password', null, new LogContext(
                action: 'keycloak_admin.reset_password',
                extra: ['ssoSubject' => $ssoSubject, 'statusCode' => $response->getStatusCode()],
            ));

            throw new ApiException('Không thể đặt lại mật khẩu', 500);
        }
    }

    public function clearRequiredActions(string $ssoSubject): void
    {
        $adminToken = $this->getAdminToken();

        $response = $this->client->request(
            'PUT',
            $this->keycloakUrl . '/admin/realms/lms/users/' . $ssoSubject,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'requiredActions' => [],
                ],
            ],
        );

        if ($response->getStatusCode() !== 204) {
            $this->logger->error('Failed to clear Keycloak required actions', null, new LogContext(
                action: 'keycloak_admin.clear_required_actions',
                extra: ['ssoSubject' => $ssoSubject, 'statusCode' => $response->getStatusCode()],
            ));

            throw new ApiException('Không thể hoàn tất đặt lại mật khẩu', 500);
        }
    }

    /**
     * @return list<string>
     */
    public function getRequiredActions(string $ssoSubject): array
    {
        $adminToken = $this->getAdminToken();

        $response = $this->client->request(
            'GET',
            $this->keycloakUrl . '/admin/realms/lms/users/' . $ssoSubject,
            [
                'headers' => ['Authorization' => 'Bearer ' . $adminToken],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new ApiException('Không thể xác thực liên kết đặt lại mật khẩu', 400);
        }

        $user = $response->toArray();
        $actions = $user['requiredActions'] ?? [];

        return is_array($actions) ? array_values(array_map('strval', $actions)) : [];
    }
}
