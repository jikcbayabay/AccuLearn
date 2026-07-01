<?php

namespace App\Http\Controllers;

use App\Services\Moodle\MoodleService;
use Illuminate\Http\JsonResponse;

class MoodleController extends Controller
{
    public function courses(MoodleService $moodle): JsonResponse
    {
        return response()->json(
            $moodle->getCourses()
        );
    }

    public function categories(MoodleService $moodle): JsonResponse
    {
        return response()->json(
            $moodle->getCategories()
        );
    }
}