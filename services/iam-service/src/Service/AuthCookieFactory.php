<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

final class AuthCookieFactory
{
    public function createAuthTokenCookie(Request $request, string $accessToken, int $expiresIn): Cookie
    {
        return Cookie::create('auth_token')
            ->withValue($accessToken)
            ->withHttpOnly(true)
            ->withSecure($request->isSecure())
            ->withSameSite(Cookie::SAMESITE_STRICT)
            ->withPath('/')
            ->withExpires(time() + $expiresIn);
    }

    public function createRefreshTokenCookie(Request $request, string $refreshToken, int $expiresIn): Cookie
    {
        return Cookie::create('refresh_token')
            ->withValue($refreshToken)
            ->withHttpOnly(true)
            ->withSecure($request->isSecure())
            ->withSameSite(Cookie::SAMESITE_STRICT)
            ->withPath('/')
            ->withExpires(time() + $expiresIn);
    }

    public function createExpiredAuthTokenCookie(Request $request): Cookie
    {
        return Cookie::create('auth_token')
            ->withValue('')
            ->withHttpOnly(true)
            ->withSecure($request->isSecure())
            ->withSameSite(Cookie::SAMESITE_STRICT)
            ->withPath('/')
            ->withExpires(1);
    }

    public function createExpiredRefreshTokenCookie(Request $request): Cookie
    {
        return Cookie::create('refresh_token')
            ->withValue('')
            ->withHttpOnly(true)
            ->withSecure($request->isSecure())
            ->withSameSite(Cookie::SAMESITE_STRICT)
            ->withPath('/')
            ->withExpires(1);
    }
}
