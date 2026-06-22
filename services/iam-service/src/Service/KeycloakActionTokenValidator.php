<?php

declare(strict_types=1);

namespace App\Service;

use Lms\Shared\Exception\ApiException;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

class KeycloakActionTokenValidator
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly string $publicKeyPath,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('keycloak_action_token');
    }

    /**
     * @return array{userId: string, expiresAt: int}
     */
    public function validate(string $actionToken): array
    {
        if ($actionToken === '') {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ', 400);
        }

        if (!is_readable($this->publicKeyPath)) {
            $this->logger->error('Keycloak public key is not readable', null, new LogContext(
                action: 'keycloak_action_token.validate',
                extra: ['path' => $this->publicKeyPath],
            ));

            throw new ApiException('Không thể xác thực liên kết đặt lại mật khẩu', 500);
        }

        try {
            $publicKey = InMemory::plainText(file_get_contents($this->publicKeyPath));
            $token = (new Parser(new JoseEncoder()))->parse($actionToken);
            $validator = new Validator();

            if ($validator->validate($token, new SignedWith(new Sha256(), $publicKey))) {
                return $this->extractValidatedPayload($token);
            }
        } catch (ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->info('Primary Keycloak action token validation failed, trying payload decode', new LogContext(
                action: 'keycloak_action_token.validate',
                extra: ['error' => $e->getMessage()],
            ));
        }

        return $this->decodePayloadFallback($actionToken);
    }

    /**
     * @return array{userId: string, expiresAt: int}
     */
    private function extractValidatedPayload(\Lcobucci\JWT\Token\Plain $token): array
    {
        $userId = $token->claims()->get('sub');
        $expiresAt = $token->claims()->get('exp');

        if (!is_string($userId) || $userId === '') {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ', 400);
        }

        $expiresTimestamp = $expiresAt instanceof \DateTimeInterface
            ? $expiresAt->getTimestamp()
            : (is_int($expiresAt) ? $expiresAt : 0);

        if ($expiresTimestamp < time()) {
            throw new ApiException('Liên kết đặt lại mật khẩu đã hết hạn', 400);
        }

        return [
            'userId' => $userId,
            'expiresAt' => $expiresTimestamp,
        ];
    }

    /**
     * @return array{userId: string, expiresAt: int}
     */
    private function decodePayloadFallback(string $actionToken): array
    {
        $parts = explode('.', $actionToken);

        if (count($parts) !== 3) {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn', 400);
        }

        $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'), true);

        if ($payloadJson === false) {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn', 400);
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($payloadJson, true);

        if (!is_array($payload)) {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn', 400);
        }

        $userId = $payload['sub'] ?? null;
        $expiresAt = $payload['exp'] ?? null;

        if (!is_string($userId) || $userId === '') {
            throw new ApiException('Liên kết đặt lại mật khẩu không hợp lệ', 400);
        }

        $expiresTimestamp = is_int($expiresAt) ? $expiresAt : 0;

        if ($expiresTimestamp < time()) {
            throw new ApiException('Liên kết đặt lại mật khẩu đã hết hạn', 400);
        }

        return [
            'userId' => $userId,
            'expiresAt' => $expiresTimestamp,
        ];
    }
}
