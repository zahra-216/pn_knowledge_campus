<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Models\CourseLevel;

class CourseLevelController extends CourseLookupController
{
    protected function modelClass(): string
    {
        return CourseLevel::class;
    }

    protected function tableName(): string
    {
        return 'course_levels';
    }
}
