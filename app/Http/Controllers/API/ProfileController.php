<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends BaseController
{
    public function edit()
    {
        return view('admin.profile.edit');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],  // Max 2MB
        ]);

        $user->name = $request->name;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
        }

        $user->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Profile updated successfully');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'name' => 'string|required',
        ]);
        $user = User::query()->where('name', '=', $request->name)->first();
        $user->delete();
        return $this->sendResponse(1, 'Your User Deleted successfully.');
    }
}
