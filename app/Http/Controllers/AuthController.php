<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            
            // Check if using default password
            if (Hash::check('12345678', Auth::user()->password)) {
                session()->flash('password_warning', true);
            }
            
            return $this->redirectToDashboard();
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    protected function redirectToDashboard()
    {
        $user = Auth::user();
        return redirect()->route($user->getHomeRoute());
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'school' => ['required', 'integer', 'exists:schools,id'],
            'role' => ['required', Rule::in(['student', 'parent', 'teacher'])],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $requestedRole = Role::query()
                ->where('guard_name', 'web')
                ->where('name', $validated['role'])
                ->first();

            if (!$requestedRole) {
                throw ValidationException::withMessages([
                    'role' => 'The selected role is not available for registration.',
                ]);
            }

            $applicantRole = Role::firstOrCreate([
                'name' => 'applicant',
                'guard_name' => 'web',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'school_id' => $validated['school'],
            ]);

            $user->assignRole($applicantRole);

            $application = AccountApplication::query()->create([
                'user_id' => $user->id,
                'role_id' => $requestedRole->id,
            ]);

            $application->setStatus('under review', 'Application submitted and awaiting review.');

            return $user;
        });

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with('status', 'Your application has been submitted for review. You will be able to access the portal after approval.');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (TransportExceptionInterface $e) {
            Log::warning('Password reset email could not be sent because mail transport failed.', [
                'email' => $request->email,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Password reset email could not be sent right now. Ask the administrator to check the mail server settings.',
                ]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
