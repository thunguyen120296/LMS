<?php

declare(strict_types=1);

namespace App\Controller;

use Lms\Shared\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/course')]
final class CourseController extends BaseController
{
    #[Route('/course', name: 'app_course')]
    public function index(): JsonResponse
    {
        return $this->success([
            'path' => 'src/Controller/CourseController.php',
        ], 'Welcome to course service');
    }

    #[Route('/course/create', name: 'app_course_create', methods: ['POST'])]
    #[IsGranted('COURSE:CREATE')]
    public function createCourse(): JsonResponse
    {
        return $this->success(null, 'Course created successfully');
    }
}
