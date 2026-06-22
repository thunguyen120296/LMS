<?php

declare(strict_types=1);

namespace App\Service;

use Lms\Shared\Exception\ApiException;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;

class ResetPasswordService
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly KeycloakAdminService $keycloakAdminService,
        private readonly KeycloakActionTokenValidator $actionTokenValidator,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('reset_password');
    }

    public function validateActionToken(string $actionToken): void
    {
        $this->assertPendingPasswordUpdate($actionToken);
    }

    public function resetPassword(string $actionToken, string $password): void
    {
        $userId = $this->assertPendingPasswordUpdate($actionToken);

        $this->keycloakAdminService->resetPassword($userId, $password);
        $this->keycloakAdminService->clearRequiredActions($userId);

        $this->logger->info('Password reset completed via IAM', new LogContext(
            action: 'reset_password.complete',
            extra: ['ssoSubject' => $userId],
        ));
    }

    private function assertPendingPasswordUpdate(string $actionToken): string
    {
        $payload = $this->actionTokenValidator->validate($actionToken);
        $requiredActions = $this->keycloakAdminService->getRequiredActions($payload['userId']);

        if (!in_array('UPDATE_PASSWORD', $requiredActions, true)) {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng', 400);
        }

        return $payload['userId'];
    }
}
