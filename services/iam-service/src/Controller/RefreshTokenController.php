<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthCookieFactory;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


final class RefreshTokenController extends BaseController
{
    #[Route('/refresh/token', name: 'app_refresh_token', methods: ['POST'])]
    public function refreshToken(
        Request $request,
        HttpClientInterface $client,
        AuthCookieFactory $cookieFactory,
    ): JsonResponse {
        $refreshToken = $request->cookies->get('refresh_token');
        if (!$refreshToken) {
            throw new ApiException('Refresh token không tồn tại', 401);
        }

        $url = $this->getParameter('keycloak_url') . '/realms/lms/protocol/openid-connect/token';
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query([
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->getParameter('client_id'),
                'client_secret' => $this->getParameter('client_secret'),
                'refresh_token' => $refreshToken,
            ]),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new ApiException('Refresh token không hợp lệ', 401);
        }

        $tokenData = $response->toArray();
        $jsonResponse = $this->success(null, 'Refresh token thành công');

        $jsonResponse->headers->setCookie(
            $cookieFactory->createAuthTokenCookie($request, $tokenData['access_token'], (int) $tokenData['expires_in']),
        );
        $jsonResponse->headers->setCookie(
            $cookieFactory->createRefreshTokenCookie($request, $tokenData['refresh_token'], (int) $tokenData['refresh_expires_in']),
        );

        return $jsonResponse;
    }
}
