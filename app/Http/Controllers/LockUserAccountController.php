<?php

namespace App\Http\Controllers;

use App\Http\Requests\LockUserAccountRequest;
use App\Models\User;

class LockUserAccountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user, LockUserAccountRequest $request)
    {
        $this->authorize('lockAccount', [$user]);

        $lock = $request->lock;

        $user->locked = $lock;
        $user->save();

        return back()->with('success', ($lock == true ? 'Locked' : 'Unlocked')." {$user->name}'s account successfully");
    }
}
