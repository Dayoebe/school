<?php

namespace App\Http\Middleware;

use App\Traits\RestrictsTeacherPortalAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictTeacherPortalAccess
{
    use RestrictsTeacherPortalAccess;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('livewire/*')) {
            return $next($request);
        }

        $user = $request->user();

        if (!$this->isRestrictedTeacherPortalUser($user)) {
            return $next($request);
        }

        if ($this->restrictedTeacherCanAccessRoute($request->route()?->getName(), $user)) {
            return $next($request);
        }

        abort(403, 'Teachers can only access teacher tools assigned to their subjects, classes, and account.');
    }
}
