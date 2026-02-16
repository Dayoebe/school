<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on the user's role.
     * Role-specific content is handled by Livewire components.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirect to role-specific dashboard if needed
        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        }
        
        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }
        
        // For admin/super_admin and parent, show default dashboard
        return view('dashboard');
    }
}