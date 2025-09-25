<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Ejecutivo;
use App\Models\Supervisor;
use App\Support\RoleHierarchy;
use Illuminate\Http\Request;

trait HandlesSupervisorContext
{
    protected function shareSupervisorContext(Request $request, ?Supervisor $supervisor): void
    {
        $supervisorId = $supervisor?->id;
        $contextQuery = $supervisorId ? ['supervisor' => $supervisorId] : [];

        $request->attributes->set('acting_supervisor_id', $supervisorId);
        $request->attributes->set('acting_supervisor', $supervisor);
        $request->attributes->set('supervisor_context_query', $contextQuery);

        view()->share([
            'actingSupervisor' => $supervisor,
            'actingSupervisorId' => $supervisorId,
            'supervisorContextQuery' => $contextQuery,
        ]);
    }

    protected function resolveSupervisorContext(Request $request, array $with = []): ?Supervisor
    {
        $user = $request->user();
        $primaryRole = RoleHierarchy::resolvePrimaryRole($user);
        $sessionKey = 'mobile.supervisor_context';
        $requestedId = (int) $request->query('supervisor');

        $request->attributes->set('acting_supervisor_role', $primaryRole);

        if ($primaryRole === 'supervisor') {
            $supervisor = Supervisor::query()
                ->with($with)
                ->firstWhere('user_id', $user?->id);

            if ($supervisor) {
                $request->session()->put($sessionKey, $supervisor->id);
                $this->shareSupervisorContext($request, $supervisor);

                return $supervisor;
            }

            $request->session()->forget($sessionKey);
            $this->shareSupervisorContext($request, null);

            return null;
        }

        $query = Supervisor::query();

        if ($primaryRole === 'ejecutivo') {
            $ejecutivo = Ejecutivo::firstWhere('user_id', $user?->id);
            abort_if(!$ejecutivo, 403, 'Perfil de ejecutivo no configurado.');

            $query->where('ejecutivo_id', $ejecutivo->id);
        } elseif (!in_array($primaryRole, ['administrativo', 'superadmin'], true)) {
            $request->session()->forget($sessionKey);
            $this->shareSupervisorContext($request, null);

            return null;
        }

        $loader = function (int $id) use ($query, $with) {
            if ($id <= 0) {
                return null;
            }

            return (clone $query)->with($with)->find($id);
        };

        if ($requestedId > 0) {
            $supervisor = $loader($requestedId);
            abort_if(!$supervisor, 403, 'Supervisor fuera de tu alcance.');

            $request->session()->put($sessionKey, $supervisor->id);
            $this->shareSupervisorContext($request, $supervisor);

            return $supervisor;
        }

        $sessionId = (int) $request->session()->get($sessionKey);
        if ($sessionId > 0) {
            $supervisor = $loader($sessionId);
            if ($supervisor) {
                $this->shareSupervisorContext($request, $supervisor);

                return $supervisor;
            }

            $request->session()->forget($sessionKey);
        }

        $supervisor = (clone $query)->with($with)
            ->orderBy('nombre')
            ->orderBy('apellido_p')
            ->orderBy('apellido_m')
            ->first();

        if ($supervisor) {
            $request->session()->put($sessionKey, $supervisor->id);
        } else {
            $request->session()->forget($sessionKey);
        }

        $this->shareSupervisorContext($request, $supervisor);

        return $supervisor;
    }
}
