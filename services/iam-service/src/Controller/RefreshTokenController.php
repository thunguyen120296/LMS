<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/iam')]
final class RefreshTokenController extends AbstractController
{
    #[Route('/refresh/token', name: 'app_refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        $refreshToken = $request->cookies->get('refresh_token');
        if (!$refreshToken) {
            throw new ApiException('Refresh token không tồn tại', 401);
        }

        $url = $this->getParameter('keycloak_url') . '/realms/master/protocol/openid-connect/token';
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query([
                'grant_type' => 'refresh_token',
                'client_id' => $this->getParameter('client_id'),
                'client_secret' => $this->getParameter('client_secret'),
                'refresh_token' => $refreshToken,
            ]),
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new ApiException('Refresh token không hợp lệ', 401);
        }

        $tokenData = $response->toArray();
        $jsonResponse = $this->success(null, 'Refresh token thành công');
        $cookie = Cookie::create('auth_token')
            ->withValue($tokenData['access_token'])
            ->withHttpOnly(true)
            ->withSecure(false)
            ->withSameSite('strict')
            ->withExpires($tokenData['expires_in']);
        $cookieRefreshToken = Cookie::create('refresh_token')
            ->withValue($tokenData['refresh_token'])
            ->withHttpOnly(true)
            ->withSecure(false)
            ->withSameSite('strict')
            ->withExpires($tokenData['refresh_expires_in']);
        $jsonResponse->headers->setCookie($cookie);
        return $jsonResponse;
    }
}
