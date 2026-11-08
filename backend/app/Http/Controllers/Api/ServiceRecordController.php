<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRecordResource;
use App\Models\ServiceRecord;
use App\Models\UserBike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bikeId = $request->query('bikeId') ?? $request->query('bike_id');

        if ($bikeId !== null && $bikeId !== '') {
            $bike = UserBike::where('id', $bikeId)
                ->where('user_id', $request->user()->id)
                ->first();

            // Ownership rule: 404 if the bike does not belong to the authenticated user
            if (!$bike) {
                return response()->json([
                    'message' => 'الموتوسيكل غير موجود أو ليس في حسابك',
                ], 404);
            }

            $records = ServiceRecord::where('user_bike_id', $bike->id)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();
        } else {
            $userBikeIds = $request->user()->bikes()->pluck('id');
            $records = ServiceRecord::whereIn('user_bike_id', $userBikeIds)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();
        }

        return response()->json([
            'data' => ServiceRecordResource::collection($records),
        ]);
    }
}
