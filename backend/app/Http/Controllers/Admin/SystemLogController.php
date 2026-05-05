<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // TODO: return paginated UsageLog entries with filters (action, user, date range).
        return response()->json([]);
    }
}
