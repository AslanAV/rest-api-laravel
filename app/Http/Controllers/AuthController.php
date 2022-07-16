<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        $user = new User();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = bcrypt($validated['password']);

        $user->save();

        return response()->json($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            abort(401, 'Unauthorized.');
        }

        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'token' => $user->createToken('API_TOKEN')->plainTextToken,
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(Auth::user());
    }
}
