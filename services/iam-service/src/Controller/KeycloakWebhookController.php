<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EmailVerificationSyncService;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class KeycloakWebhookController extends BaseController
{
    private readonly BaseLogService $logger;

    public function __construct(BaseLogService $logger)
    {
        $this->logger = $logger->for('keycloak_webhook');
    }

    #[Route('/webhooks/keycloak', name: 'app_keycloak_webhook', methods: ['POST'])]
    public function handle(
        Request $request,
        EmailVerificationSyncService $verificationSyncService,
    ): JsonResponse {
        $expectedSecret = (string) $this->getParameter('keycloak_webhook_secret');
        $providedSecret = $request->headers->get('X-Keycloak-Webhook-Secret', '');

        if ($expectedSecret === '' || !hash_equals($expectedSecret, $providedSecret)) {
            throw new ApiException('Unauthorized', 401);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        $eventType = (string) ($payload['type'] ?? '');
        $ssoSubject = (string) ($payload['userId'] ?? '');

        if ($ssoSubject === '') {
            throw new ApiException('Thiếu userId trong payload', 400);
        }

        $this->logger->info('Keycloak webhook received', new LogContext(
            action: 'keycloak_webhook.receive',
            extra: ['type' => $eventType, 'ssoSubject' => $ssoSubject],
        ));

        if (!in_array($eventType, ['VERIFY_EMAIL', 'UPDATE_EMAIL'], true)) {
            return $this->success(null, 'Event ignored');
        }

        $verificationSyncService->syncFromKeycloakUserId($ssoSubject);

        return $this->success(null, 'Email verification synced');
    }
}
