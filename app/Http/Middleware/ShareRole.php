<?php

namespace App\Http\Middleware;

use App\Support\RoleHierarchy;
use Closure;
use Illuminate\Http\Request;

class ShareRole
{
    /**
     * Share both the user's primary role and the active mobile section role with every view.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);
        $sectionRole = $request->attributes->get('mobile_section_role')
            ?? RoleHierarchy::defaultSection($primaryRole);

        $request->attributes->set('mobile_section_role', $sectionRole);
        if ($primaryRole) {
            $request->attributes->set('user_primary_role', $primaryRole);
        }

        view()->share([
            'role' => $sectionRole,
            'userRole' => $primaryRole,
        ]);

        return $next($request);
    }
}
