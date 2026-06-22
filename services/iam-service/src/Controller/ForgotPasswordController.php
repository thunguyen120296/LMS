<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ForgotPasswordService;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ForgotPasswordController extends BaseController
{
    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        ForgotPasswordService $forgotPasswordService,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        if (empty($payload['email']) || !is_string($payload['email'])) {
            throw new ApiException('Vui lòng nhập email', 400);
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('Email không hợp lệ', 400);
        }

        $forgotPasswordService->requestReset($payload['email']);

        return $this->success(null, ForgotPasswordService::GENERIC_SUCCESS_MESSAGE);
    }
}
