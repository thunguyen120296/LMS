<?php

namespace App\Enum;

enum CourseLevel: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
    case AllLevels = 'all_levels';
}