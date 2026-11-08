<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->orderBy('sort')
            ->get();

        return response()->json(ServiceResource::collection($services));
    }
}
