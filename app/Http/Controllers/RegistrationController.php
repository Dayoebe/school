<?php

namespace App\Http\Controllers;

use App\Events\AccountStatusChanged;
use App\Http\Requests\RegistrationRequest;
use App\Models\AccountApplication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RegistrationController extends Controller
{
    public function registerView()
    {
        return view('auth.register');
    }

    public function register(RegistrationRequest $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'other_names' => 'nullable|string|max:255',
        ]);

        $role = Role::findOrFail($request->role);

        $name = $request->name ?? trim(sprintf(
            '%s %s %s',
            $request->first_name ?? '',
            $request->last_name ?? '',
            $request->other_names ?? ''
        ));
        $name = trim($name) !== '' ? trim($name) : $request->email;

        $user = DB::transaction(function () use ($request, $name, $role) {
            $user = User::create([
                'name' => $name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'school_id' => $request->school,
                'gender' => $request->gender,
                'birthday' => $request->birthday,
                'nationality' => $request->nationality,
                'state' => $request->state,
                'city' => $request->city,
                'religion' => $request->religion,
                'blood_group' => $request->blood_group,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $user->assignRole('applicant');

            AccountApplication::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);

            return $user;
        });

        $accountApplication = $user->accountApplication;
        $status = 'Application Received';
        $reason = 'Application has been received, we would reach out to you for further information';
        $accountApplication?->setStatus($status, $reason);

        Auth::login($user);

        AccountStatusChanged::dispatch($user, $status, $reason);

        return back()->with('success', 'Registration complete, you would receive an email to verify your account');
    }
}
