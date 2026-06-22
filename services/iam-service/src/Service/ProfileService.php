<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\KeycloakUser;
use Lms\Shared\Exception\ApiException;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;

class ProfileService
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly KeycloakAdminService $keycloakAdminService,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('profile');
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfile(KeycloakUser $authUser): array
    {
        $user = $this->resolveUser($authUser);

        return $this->formatProfile($user);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function updateProfile(KeycloakUser $authUser, array $payload): array
    {
        $user = $this->resolveUser($authUser);
        $nameChanged = $this->applyUpdates($user, $payload);

        if ($nameChanged && $user->getSsoSubject() !== null) {
            $this->keycloakAdminService->updateUserProfile(
                $user->getSsoSubject(),
                $user->getFirstName(),
                $user->getLastName(),
            );
        }

        $this->userRepository->save($user, true);

        $this->logger->info('User profile updated', new LogContext(
            action: 'profile.update',
            extra: ['userId' => $user->getId()],
        ));

        return $this->formatProfile($user);
    }

    private function resolveUser(KeycloakUser $authUser): User
    {
        $user = $this->userRepository->findByEmail($authUser->getUserIdentifier());

        if ($user === null) {
            throw new ApiException('Không tìm thấy người dùng', 404);
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function applyUpdates(User $user, array $payload): bool
    {
        $allowedFields = ['firstName', 'lastName', 'fullName', 'avatarUrl', 'locale'];
        $hasUpdate = false;

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $payload)) {
                $hasUpdate = true;
                break;
            }
        }

        if (!$hasUpdate) {
            throw new ApiException('Không có dữ liệu để cập nhật', 400);
        }

        $nameChanged = false;
        $previousFirstName = $user->getFirstName();
        $previousLastName = $user->getLastName();

        if (array_key_exists('fullName', $payload)) {
            $fullName = trim((string) $payload['fullName']);

            if ($fullName === '') {
                $user->setFirstName(null);
                $user->setLastName(null);
            } else {
                $nameParts = explode(' ', $fullName, 2);
                $user->setFirstName($nameParts[0]);
                $user->setLastName($nameParts[1] ?? null);
            }

            $nameChanged = true;
        } else {
            if (array_key_exists('firstName', $payload)) {
                $firstName = $this->normalizeOptionalString($payload['firstName'], 'firstName', 100);
                $user->setFirstName($firstName);
                $nameChanged = true;
            }

            if (array_key_exists('lastName', $payload)) {
                $lastName = $this->normalizeOptionalString($payload['lastName'], 'lastName', 100);
                $user->setLastName($lastName);
                $nameChanged = true;
            }
        }

        if ($nameChanged) {
            $nameChanged = $previousFirstName !== $user->getFirstName()
                || $previousLastName !== $user->getLastName();
        }

        if (array_key_exists('avatarUrl', $payload)) {
            $avatarUrl = $this->normalizeOptionalString($payload['avatarUrl'], 'avatarUrl', 2048);

            if ($avatarUrl !== null && !filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
                throw new ApiException('URL ảnh đại diện không hợp lệ', 400, [
                    ['field' => 'avatarUrl', 'message' => 'URL ảnh đại diện không hợp lệ'],
                ]);
            }

            $user->setAvatarUrl($avatarUrl);
        }

        if (array_key_exists('locale', $payload)) {
            $locale = trim((string) $payload['locale']);

            if ($locale === '') {
                throw new ApiException('Ngôn ngữ không hợp lệ', 400, [
                    ['field' => 'locale', 'message' => 'Ngôn ngữ không được để trống'],
                ]);
            }

            if (strlen($locale) > 10) {
                throw new ApiException('Ngôn ngữ không hợp lệ', 400, [
                    ['field' => 'locale', 'message' => 'Ngôn ngữ không được vượt quá 10 ký tự'],
                ]);
            }

            $user->setLocale($locale);
        }

        return $nameChanged;
    }

    private function normalizeOptionalString(mixed $value, string $field, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        if (strlen($normalized) > $maxLength) {
            throw new ApiException('Dữ liệu không hợp lệ', 400, [
                ['field' => $field, 'message' => sprintf('%s không được vượt quá %d ký tự', $field, $maxLength)],
            ]);
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProfile(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'avatarUrl' => $user->getAvatarUrl(),
            'locale' => $user->getLocale(),
            'emailVerified' => $user->isEmailVerified(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $user->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
