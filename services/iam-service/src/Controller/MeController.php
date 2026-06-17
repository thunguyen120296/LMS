<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\KeycloakUser;
use App\Service\KeycloakClaimsReader;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MeController extends BaseController
{
    #[Route('/me', name: 'app_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(
        UserRepository $userRepository,
        KeycloakClaimsReader $claimsReader,
    ): JsonResponse {
        $authUser = $this->getUser();

        // 1. SỬA ĐỔI: Ép kiểu chuẩn theo Class Stateless của bạn
        if (!$authUser instanceof KeycloakUser) {
            throw new ApiException('Không thể xác định người dùng', 401);
        }

        $email = $authUser->getUserIdentifier();

        // 2. Lấy thông tin mở rộng (như fullName) từ DB cục bộ nếu cần thiết
        $localUser = $userRepository->findByEmail($email);

        return $this->success([
            'user_info' => [
                'id' => $localUser ? $localUser->getId() : null,
                'email' => $email,
                'fullName' => $localUser ? $localUser->getFullName() : null,
            ],
            'permissions' => $claimsReader->getPermissions(),
            'roles' => $claimsReader->getRealmRoles(),
        ]);
    }
}