<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellRequestRequest;
use App\Models\SellRequest;
use Illuminate\Http\JsonResponse;

class SellRequestController extends Controller
{
    public function store(SellRequestRequest $request): JsonResponse
    {
        $modelCode = $request->input('bikeModel');
        $cleanModelCode = preg_replace('/^Zontes\s+/i', '', $modelCode);

        $sellRequest = SellRequest::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'bike_model_code' => $cleanModelCode,
            'year' => (int) $request->input('year'),
            'km' => (int) $request->input('km'),
            'notes' => $request->input('notes'),
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'تم استلام طلب بيع موتوسيكلك بنجاح، وسيتواصل معك مهندسو الجراج لتحديد موعد الفحص.',
            'id' => $sellRequest->id,
        ], 201);
    }
}
