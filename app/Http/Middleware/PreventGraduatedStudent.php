<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventGraduatedStudent
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->hasRole('student')) {
            return $next($request);
        }

        $studentRecord = auth()->user()
            ->studentRecord()
            ->withoutGlobalScopes()
            ->with('baseClass')
            ->first();

        if (!$studentRecord) {
            session()->flash('danger', 'You cannot access this resource because your student record is not available.');

            return redirect('dashboard');
        }

        if ($studentRecord->is_graduated === true) {
            session()->flash('danger', 'You cannot access this resource because you have been marked as graduated');

            return redirect('dashboard');
        }

        if (!$studentRecord->isActiveStudent()) {
            session()->flash('danger', 'You cannot access this resource because your student account is not currently active.');

            return redirect('dashboard');
        }

        return $next($request);
    }
}
