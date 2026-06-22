<?php

declare(strict_types=1);

namespace App\Service;

use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;

class ForgotPasswordService
{
    public const GENERIC_SUCCESS_MESSAGE = 'Nếu email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn tới địa chỉ email của bạn';

    private const ACTION_TOKEN_LIFESPAN_SECONDS = 3600;

    private readonly BaseLogService $logger;

    public function __construct(
        private readonly KeycloakAdminService $keycloakAdminService,
        private readonly string $frontendUrl,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('forgot_password');
    }

    public function requestReset(string $email): void
    {
        $normalizedEmail = trim($email);

        $this->logger->info('Password reset requested', new LogContext(
            action: 'forgot_password.request',
            extra: ['email' => $normalizedEmail],
        ));

        $keycloakUser = $this->keycloakAdminService->findUserByEmail($normalizedEmail);

        if ($keycloakUser === null || !$keycloakUser['enabled']) {
            $this->logger->info('Password reset skipped: user not found or disabled in Keycloak', new LogContext(
                action: 'forgot_password.request',
                extra: ['email' => $normalizedEmail],
            ));

            return;
        }

        $redirectUri = rtrim($this->frontendUrl, '/') . '/reset-password';

        $this->keycloakAdminService->sendUpdatePasswordEmail(
            $keycloakUser['id'],
            $redirectUri,
            self::ACTION_TOKEN_LIFESPAN_SECONDS,
        );

        $this->logger->info('Keycloak UPDATE_PASSWORD email triggered', new LogContext(
            action: 'forgot_password.request',
            extra: [
                'email' => $normalizedEmail,
                'ssoSubject' => $keycloakUser['id'],
            ],
        ));
    }
}
