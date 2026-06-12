<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RegisterService;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/iam')]
final class RegisterController extends BaseController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        RegisterService $registerService,
    ): JsonResponse {
        $start = microtime(true);
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        if (empty($payload['email']) || empty($payload['password']) || empty($payload['fullName'])) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('Email không hợp lệ', 400);
        }

        if (strlen($payload['password']) < 8) {
            throw new ApiException('Mật khẩu phải có ít nhất 8 ký tự', 400);
        }

        if (strlen($payload['password']) > 128) {
            throw new ApiException('Mật khẩu không được vượt quá 128 ký tự', 400);
        }

        if (!preg_match('/[A-Z]/', $payload['password'])) {
            throw new ApiException('Mật khẩu phải có ít nhất 1 chữ hoa', 400);
        }

        if (!preg_match('/[0-9]/', $payload['password'])) {
            throw new ApiException('Mật khẩu phải có ít nhất 1 chữ số', 400);
        }

        $user = $registerService->createUser($payload);
        $end = microtime(true);
        error_log('Time taken: ' . ($end - $start) . ' seconds');
        return $this->success([
            'userId' => $user->getId(),
            'email' => $user->getEmail(),
        ], 201);
    }
}
