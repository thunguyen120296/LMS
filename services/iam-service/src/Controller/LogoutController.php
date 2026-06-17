<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthCookieFactory;
use Lms\Shared\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController extends BaseController
{
    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(Request $request, AuthCookieFactory $cookieFactory): JsonResponse
    {
        $response = $this->success(null, 'Đăng xuất thành công');

        $response->headers->setCookie($cookieFactory->createExpiredAuthTokenCookie($request));
        $response->headers->setCookie($cookieFactory->createExpiredRefreshTokenCookie($request));

        return $response;
    }
}
