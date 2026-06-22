<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\AuthCookieFactory;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class LoginController extends BaseController
{
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(
        Request $request,
        HttpClientInterface $client,
        AuthCookieFactory $cookieFactory,
        UserRepository $userRepository,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        if (empty($data['username']) || empty($data['password'])) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        $localUser = $userRepository->findByEmail(trim((string) $data['username']));
        if ($localUser !== null && !$localUser->isEmailVerified()) {
            throw new ApiException('Vui lòng xác minh email trước khi đăng nhập. Kiểm tra hộp thư của bạn.', 403, [
                ['field' => 'email', 'message' => 'Email chưa được xác minh'],
            ]);
        }

        $url = $this->getParameter('keycloak_url') . '/realms/lms/protocol/openid-connect/token';
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query([
                'grant_type'    => $this->getParameter('grant_type'),
                'client_id'     => $this->getParameter('client_id'),
                'client_secret' => $this->getParameter('client_secret'),
                'username'      => $data['username'],
                'password'      => $data['password'],
            ]),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new ApiException('Đăng nhập thất bại', 401);
        }

        $tokenData = $response->toArray();
        $jsonResponse = $this->success(null, 'Đăng nhập thành công');

        $jsonResponse->headers->setCookie(
            $cookieFactory->createAuthTokenCookie($request, $tokenData['access_token'], (int) $tokenData['expires_in']),
        );
        $jsonResponse->headers->setCookie(
            $cookieFactory->createRefreshTokenCookie($request, $tokenData['refresh_token'], (int) $tokenData['refresh_expires_in']),
        );

        return $jsonResponse;
    }
}
