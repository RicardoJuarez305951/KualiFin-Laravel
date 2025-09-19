<?php

namespace App\Http\Middleware;

use App\Support\RoleHierarchy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleCanAccessMobileSection
{
    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next, string $sectionRole): Response
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);
        $defaultSection = RoleHierarchy::defaultSection($primaryRole);

        if ($user && $request->routeIs("mobile.$sectionRole.index") && RoleHierarchy::normalize($sectionRole) !== $defaultSection) {
            return redirect()->route("mobile.{$defaultSection}.index");
        }

        if (!$user || !RoleHierarchy::canAccess($primaryRole, $sectionRole)) {
            abort(403, 'No autorizado.');
        }

        $request->attributes->set('mobile_section_role', $sectionRole);
        if ($primaryRole) {
            $request->attributes->set('user_primary_role', $primaryRole);
        }

        return $next($request);
    }
}
