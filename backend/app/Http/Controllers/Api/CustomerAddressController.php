<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses;

        return response()->json(AddressResource::collection($addresses));
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'governorate' => $request->input('governorate'),
            'area' => $request->input('area'),
            'street' => $request->input('street'),
            'is_default' => $isDefault,
        ]);

        return response()->json(new AddressResource($address), 201);
    }

    public function update(AddressRequest $request, $id): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($id);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update([
            'governorate' => $request->input('governorate'),
            'area' => $request->input('area'),
            'street' => $request->input('street'),
            'is_default' => $request->has('is_default') ? $request->boolean('is_default') : $address->is_default,
        ]);

        return response()->json(new AddressResource($address));
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'تم حذف العنوان بنجاح',
        ]);
    }
}
