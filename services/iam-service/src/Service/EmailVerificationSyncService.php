<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Lms\Shared\Exception\ApiException;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;

class EmailVerificationSyncService
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly KeycloakAdminService $keycloakAdminService,
        private readonly KeycloakActionTokenValidator $actionTokenValidator,
        private readonly IamEventPublisher $eventPublisher,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('email_verification_sync');
    }

    public function completeFromActionToken(string $actionToken): User
    {
        $payload = $this->actionTokenValidator->validate($actionToken);
        $ssoSubject = $payload['userId'];

        $this->assertPendingEmailVerification($ssoSubject);

        return $this->syncFromKeycloakUserId($ssoSubject);
    }

    public function syncFromKeycloakUserId(string $ssoSubject): User
    {
        $keycloakUser = $this->keycloakAdminService->getUserById($ssoSubject);

        if ($keycloakUser === null) {
            throw new ApiException('Không tìm thấy người dùng trên hệ thống xác thực', 404);
        }

        if (!$keycloakUser['emailVerified']) {
            $this->keycloakAdminService->markEmailVerified($ssoSubject);
            $keycloakUser['emailVerified'] = true;
        }

        $user = $this->userRepository->findBySso('keycloak', $ssoSubject);

        if ($user === null) {
            throw new ApiException('Không tìm thấy người dùng trong hệ thống', 404);
        }

        return $this->markVerifiedLocally($user, $keycloakUser['email']);
    }

    private function assertPendingEmailVerification(string $ssoSubject): void
    {
        $requiredActions = $this->keycloakAdminService->getRequiredActions($ssoSubject);

        if (!in_array('VERIFY_EMAIL', $requiredActions, true)) {
            $keycloakUser = $this->keycloakAdminService->getUserById($ssoSubject);

            if ($keycloakUser !== null && $keycloakUser['emailVerified']) {
                return;
            }

            throw new ApiException('Liên kết xác minh không hợp lệ hoặc đã được sử dụng', 400);
        }
    }

    private function markVerifiedLocally(User $user, string $email): User
    {
        $wasVerified = $user->isEmailVerified();

        $user->setEmailVerified(true);
        $this->userRepository->save($user, true);

        $this->logger->info('Email verification synced to IAM database', new LogContext(
            action: 'email_verification_sync.complete',
            userId: $user->getId(),
            extra: ['email' => $email, 'ssoSubject' => $user->getSsoSubject()],
        ));

        if (!$wasVerified) {
            $this->eventPublisher->publishUserVerified($user);
        }

        return $user;
    }
}
