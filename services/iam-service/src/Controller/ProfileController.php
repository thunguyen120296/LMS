<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\KeycloakUser;
use App\Service\ProfileService;
use Lms\Shared\Controller\BaseController;
use Lms\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends BaseController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(ProfileService $profileService): JsonResponse
    {
        $authUser = $this->getUser();

        if (!$authUser instanceof KeycloakUser) {
            throw new ApiException('Không thể xác định người dùng', 401);
        }

        return $this->success($profileService->getProfile($authUser));
    }

    #[Route('/update-profile', name: 'app_update_profile', methods: ['POST', 'PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateProfile(Request $request, ProfileService $profileService): JsonResponse
    {
        $authUser = $this->getUser();

        if (!$authUser instanceof KeycloakUser) {
            throw new ApiException('Không thể xác định người dùng', 401);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            throw new ApiException('Dữ liệu không hợp lệ', 400);
        }

        $profile = $profileService->updateProfile($authUser, $payload);

        return $this->success($profile, 'Cập nhật hồ sơ thành công');
    }
}
