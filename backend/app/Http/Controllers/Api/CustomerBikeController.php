<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserBikeRequest;
use App\Http\Resources\UserBikeResource;
use App\Models\BikeModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerBikeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bikes = $request->user()->bikes()->with('latestServiceRecord')->get();

        return response()->json(UserBikeResource::collection($bikes));
    }

    public function store(UserBikeRequest $request): JsonResponse
    {
        $user = $request->user();
        $cleanModelCode = $request->input('bikeModelCode');
        $bikeModel = BikeModel::where('code', $cleanModelCode)->firstOrFail();

        $licenseUntil = $request->input('licenseUntil') ?: $request->input('license_until');

        $bike = $user->bikes()->create([
            'bike_model_code' => $bikeModel->code,
            'model_name' => $bikeModel->name ?: ('Zontes ' . $bikeModel->code),
            'year' => (int) $request->input('year', date('Y')),
            'plate' => $request->input('plate'),
            'odometer' => (int) $request->input('odometer', 0),
            'next_service_km' => ((int) $request->input('odometer', 0)) + 3000,
            'license_until' => $licenseUntil,
            'color' => $request->input('color'),
        ]);

        return response()->json(new UserBikeResource($bike), 201);
    }

    public function update(UserBikeRequest $request, $id): JsonResponse
    {
        $bike = $request->user()->bikes()->findOrFail($id);

        $data = [];
        if ($request->has('bikeModelCode')) {
            $bikeModel = BikeModel::where('code', $request->input('bikeModelCode'))->firstOrFail();
            $data['bike_model_code'] = $bikeModel->code;
            $data['model_name'] = $bikeModel->name ?: ('Zontes ' . $bikeModel->code);
        }
        if ($request->has('year')) {
            $data['year'] = (int) $request->input('year');
        }
        if ($request->has('plate')) {
            $data['plate'] = $request->input('plate');
        }
        if ($request->has('odometer')) {
            $data['odometer'] = (int) $request->input('odometer');
        }
        if ($request->has('color')) {
            $data['color'] = $request->input('color');
        }
        if ($request->has('licenseUntil') || $request->has('license_until')) {
            $data['license_until'] = $request->input('licenseUntil') ?: $request->input('license_until');
        }

        $bike->update($data);

        return response()->json(new UserBikeResource($bike));
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $bike = $request->user()->bikes()->findOrFail($id);
        $bike->delete();

        return response()->json([
            'message' => 'تم حذف الموتوسيكل بنجاح',
        ]);
    }
}
