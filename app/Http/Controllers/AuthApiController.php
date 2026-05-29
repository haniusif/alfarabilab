<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /** تسجيل الدخول وإصدار رمز Sanctum للتطبيق */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'device'   => ['nullable', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('Invalid credentials')],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => [__('This account is inactive')],
            ]);
        }

        $token = $user->createToken($data['device'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    /** بيانات المستخدم الحالي */
    public function me(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    /** إلغاء الرمز الحالي (خروج) */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('Logged out')]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role->value,
            'specialty' => $user->specialty,
        ];
    }
}
