<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class DoctorProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('doctor.profile', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title'      => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'specialty'  => ['nullable', 'string', 'max:100'],
            'avatar'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $payload = [
            'title'      => $data['title'] ?? null,
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'specialty'  => $data['specialty'] ?? null,
            'name'       => User::composeName($data['title'] ?? null, $data['first_name'], $data['last_name']),
        ];

        // إزالة الصورة الحالية إن طُلب ذلك أو رُفعت صورة جديدة
        if (! empty($data['remove_avatar']) || $request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $payload['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            $payload['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($payload);

        return back()->with('status', __('Profile updated'));
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', __('Password changed'));
    }
}
