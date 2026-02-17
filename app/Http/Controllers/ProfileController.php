<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Import Rule for validation

class ProfileController extends Controller
{
    /**
     * Show the form for changing the user's password.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle the password change request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password changed successfully');
    }

    /**
     * Show the form for editing the user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Pass the authenticated user to the view
        return view('profile.edit', ['user' => auth()->user()]);
    }

    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // Add other profile fields you want to update, e.g., 'phone', 'address'
        ]);

        $user->forceFill([
            'name' => $request->name,
            'email' => $request->email,
            // Update other fields here
        ])->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
