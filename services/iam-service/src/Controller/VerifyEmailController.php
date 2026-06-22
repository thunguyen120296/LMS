<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EmailVerificationSyncService;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class VerifyEmailController extends BaseController
{
    #[Route('/verify-email', name: 'app_verify_email', methods: ['GET'])]
    public function verify(Request $request, EmailVerificationSyncService $verificationSyncService): JsonResponse
    {
        $key = $request->query->getString('key');

        if ($key === '') {
            throw new ApiException('Liên kết xác minh không hợp lệ', 400);
        }

        $verificationSyncService->completeFromActionToken($key);

        return $this->success(null, 'Email đã được xác minh thành công. Vui lòng đăng nhập.');
    }
}
