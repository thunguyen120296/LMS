<?php

declare(strict_types=1);

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent; // Đảm bảo use đúng class này
use Symfony\Component\HttpFoundation\JsonResponse;

final class JwtFailureListener
{
    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $exception = $event->getException();
        $event->setResponse(new JsonResponse([
            'status' => 'invalid_token',
            'reason' => $exception->getMessage(),
        ], 401));
    }

    // THÊM HÀM NÀY VÀO ĐỂ BẮT CHẶNG KHÔNG TÌM THẤY / TOKEN HỎNG CẤU TRÚC
    public function onJWTNotFound(JWTNotFoundEvent $event): void
    {
        $request = $event->getRequest();
        
        $event->setResponse(new JsonResponse([
            'status' => 'not_found_or_malformed_token',
            'message' => 'Lexik không tìm thấy hoặc từ chối cấu trúc token trong Cookie!',
            'cookie_received' => $request->cookies->has('auth_token'), // Check xem Symfony Request có giữ cookie không
            'cookie_value_length' => strlen((string) $request->cookies->get('auth_token')),
        ], 401));
    }
}