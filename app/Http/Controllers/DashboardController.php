<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    /**
     * Display the unified dashboard.
     */
    public function index()
    {
        return view('dashboard');
    }
}
