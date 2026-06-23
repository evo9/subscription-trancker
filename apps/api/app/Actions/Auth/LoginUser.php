<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LoginUser
{
    /**
     * @return array{user: User, token: string}
     */
    public function handle(Request $request): array
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            abort(401, 'Invalid credentials.');
        }

        /** @var User $user */
        $user = Auth::user();

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }
}
