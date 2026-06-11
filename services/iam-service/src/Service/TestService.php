<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;

class TestService
{

    public function editCourse(Security $security): string
    {
        if ($security->isGranted('PERMISSION_EDIT_COURSE')) {
            return 'User has permission to edit the course.';
        }

        return 'User does NOT have permission to edit the course.';
    }

}
