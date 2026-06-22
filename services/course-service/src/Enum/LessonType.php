<?php

namespace App\Enum;

enum LessonType: string
{
    case Video = 'video';
    case Audio = 'audio';
    case Text = 'text';
    case Quiz = 'quiz';
    case Assignment = 'assignment';
}