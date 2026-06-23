<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;

final class RegisterUser
{
    /**
     * @return array{user: User, token: string}
     */
    public function handle(RegisterRequest $request): array
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }
}
