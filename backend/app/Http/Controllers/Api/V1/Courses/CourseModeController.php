<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Models\CourseMode;

class CourseModeController extends CourseLookupController
{
    protected function modelClass(): string
    {
        return CourseMode::class;
    }

    protected function tableName(): string
    {
        return 'course_modes';
    }
}
