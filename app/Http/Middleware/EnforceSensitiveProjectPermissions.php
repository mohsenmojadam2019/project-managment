<?php

namespace App\Http\Middleware;

use App\Helper\UserService;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;

class EnforceSensitiveProjectPermissions
{
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()?->getName();

        if (!in_array($routeName, [
            'projects.update_status',
            'projects.archive_delete',
            'projects.assign_project_admin',
        ], true)) {
            return $next($request);
        }

        // Let the normal auth middleware handle unauthenticated requests.
        if (!$request->user()) {
            return $next($request);
        }

        if ($routeName === 'projects.assign_project_admin') {
            abort_403(user()->permission('edit_project_members') !== 'all');

            $projectId = $request->input('projectId');
            abort_403(empty($projectId));
            Project::findOrFail($projectId);

            return $next($request);
        }

        $projectId = $request->route('id');
        $project = Project::withTrashed()->findOrFail($projectId);

        if ($routeName === 'projects.archive_delete') {
            $permission = user()->permission('delete_projects');
            $userId = UserService::getUserId();

            abort_403(!(
                $permission === 'all'
                || ($permission === 'added' && (int) $project->added_by === (int) $userId)
            ));

            return $next($request);
        }

        abort_403(!$this->canEditProject($project));

        return $next($request);
    }

    private function canEditProject(Project $project): bool
    {
        $permission = user()->permission('edit_projects');
        $userId = UserService::getUserId();
        $isClient = in_array('client', user_roles());
        $isEmployee = in_array('employee', user_roles());
        $isMember = $project->members()->where('user_id', $userId)->exists();
        $isAddedBy = (int) $project->added_by === (int) $userId;
        $isClientOwner = (int) $project->client_id === (int) $userId;

        return $permission === 'all'
            || ($permission === 'added' && $isAddedBy)
            || ($permission === 'owned' && (($isClient && $isClientOwner) || ($isEmployee && $isMember)))
            || ($permission === 'both' && ($isAddedBy || $isClientOwner || ($isEmployee && $isMember)));
    }
}
