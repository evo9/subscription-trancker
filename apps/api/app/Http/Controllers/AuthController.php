<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUser;
use App\Actions\Auth\LogoutUser;
use App\Actions\Auth\RegisterUser;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $action): JsonResponse
    {
        ['user' => $user, 'token' => $token] = $action->handle($request);

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ], 201);
    }

    public function login(LoginRequest $request, LoginUser $action): JsonResponse
    {
        ['token' => $token] = $action->handle($request);

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request, LogoutUser $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $action->handle($user);

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }
}
