<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ResetPasswordService;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPasswordController extends BaseController
{
    #[Route('/reset-password/validate', name: 'app_reset_password_validate', methods: ['GET'])]
    public function validate(Request $request, ResetPasswordService $resetPasswordService): JsonResponse
    {
        $key = $request->query->getString('key');

        if ($key === '') {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ', 400);
        }

        $resetPasswordService->validateActionToken($key);

        return $this->success(null, 'Liên kết đặt lại mật khẩu hợp lệ.');
    }

    #[Route('/reset-password', name: 'app_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        ResetPasswordService $resetPasswordService,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        if (empty($payload['key']) || !is_string($payload['key'])) {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ', 400);
        }

        if (empty($payload['password']) || !is_string($payload['password'])) {
            throw new ApiException('Vui lòng nhập mật khẩu mới', 400);
        }

        if (empty($payload['confirmPassword']) || !is_string($payload['confirmPassword'])) {
            throw new ApiException('Vui lòng xác nhận mật khẩu mới', 400);
        }

        $this->validatePassword($payload['password'], $payload['confirmPassword']);

        $resetPasswordService->resetPassword($payload['key'], $payload['password']);

        return $this->success(null, 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
    }

    private function validatePassword(string $password, string $confirmPassword): void
    {
        if ($password !== $confirmPassword) {
            throw new ApiException('Mật khẩu xác nhận không khớp', 400, [
                ['field' => 'confirmPassword', 'message' => 'Mật khẩu xác nhận không khớp'],
            ]);
        }

        if (strlen($password) < 8) {
            throw new ApiException('Mật khẩu phải có ít nhất 8 ký tự', 400, [
                ['field' => 'password', 'message' => 'Mật khẩu phải có ít nhất 8 ký tự'],
            ]);
        }

        if (strlen($password) > 128) {
            throw new ApiException('Mật khẩu không được vượt quá 128 ký tự', 400, [
                ['field' => 'password', 'message' => 'Mật khẩu không được vượt quá 128 ký tự'],
            ]);
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new ApiException('Mật khẩu phải có ít nhất 1 chữ hoa', 400, [
                ['field' => 'password', 'message' => 'Mật khẩu phải có ít nhất 1 chữ hoa'],
            ]);
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new ApiException('Mật khẩu phải có ít nhất 1 chữ số', 400, [
                ['field' => 'password', 'message' => 'Mật khẩu phải có ít nhất 1 chữ số'],
            ]);
        }
    }
}
