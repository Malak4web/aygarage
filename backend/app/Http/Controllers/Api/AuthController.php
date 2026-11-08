<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateMeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserBike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'password' => $request->input('password'),
            'role' => 'customer',
        ]);

        if ($request->filled('bikeModel')) {
            $modelCode = $request->input('bikeModel');
            UserBike::create([
                'user_id' => $user->id,
                'bike_model_code' => $modelCode,
                'model_name' => 'Zontes ' . $modelCode,
                'year' => (int) date('Y'),
                'odometer' => 0,
                'next_service_km' => 1000,
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load(['addresses', 'bikes'])),
            'message' => 'تم إنشاء الحساب بنجاح',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $phone = $request->input('phone');
        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة',
                'errors' => [
                    'phone' => ['رقم الموبايل أو كلمة المرور غير صحيحة'],
                ],
            ], 422);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load(['addresses', 'bikes'])),
            'message' => 'تم تسجيل الدخول بنجاح',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()->load(['addresses', 'bikes'])));
    }

    public function updateMe(UpdateMeRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }

        if ($request->has('email')) {
            $user->email = $request->input('email');
        }

        if ($request->filled('new_password')) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'message' => 'كلمة المرور الحالية غير صحيحة',
                    'errors' => [
                        'current_password' => ['كلمة المرور الحالية غير صحيحة'],
                    ],
                ], 422);
            }
            $user->password = $request->input('new_password');
            $user->save();

            $currentTokenId = $request->user()->currentAccessToken()?->id;
            if ($currentTokenId) {
                $user->tokens()->where('id', '!=', $currentTokenId)->delete();
            }
        } else {
            $user->save();
        }

        return response()->json([
            'user' => new UserResource($user->load(['addresses', 'bikes'])),
            'message' => 'تم تحديث بيانات الحساب بنجاح',
        ]);
    }
}
