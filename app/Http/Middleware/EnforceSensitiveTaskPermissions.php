<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\Task;
use Closure;
use Illuminate\Http\Request;

class EnforceSensitiveTaskPermissions
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $viewRoutes = [
            'tasks.reminder',
            'tasks.check_task',
            'tasks.members',
        ];

        $editRoutes = [
            'tasks.gantt_task_update',
        ];

        $statusRoutes = [
            'tasks.send_approval',
            'tasks.show_status_reason_modal',
            'tasks.store_comment_on_change_status',
        ];

        if ($request->routeIs(...$viewRoutes)) {
            $task = $this->resolveTask($request);
            abort_403(!$this->canAccessTask($task, 'view_tasks'));
        }

        if ($request->routeIs(...$editRoutes)) {
            $task = $this->resolveTask($request);
            abort_403(!$this->canAccessTask($task, 'edit_tasks'));
        }

        if ($request->routeIs(...$statusRoutes)) {
            $task = $this->resolveTask($request);
            abort_403(!$this->canAccessTask($task, 'change_status'));
        }

        if ($request->routeIs('tasks.clientDetail')) {
            $projectId = $request->input('id');
            $project = Project::with('members')->findOrFail($projectId);
            abort_403(!$this->canViewProject($project));
        }

        if ($request->routeIs('tasks.project_tasks')) {
            $projectId = $request->route('id');

            if ($projectId && (int) $projectId !== 0) {
                $project = Project::with('members')->findOrFail($projectId);
                abort_403(!$this->canViewProject($project));
            }
        }

        return $next($request);
    }

    private function resolveTask(Request $request): Task
    {
        $taskId = $request->route('taskID')
            ?? $request->route('id')
            ?? $request->input('taskId')
            ?? $request->input('id');

        return Task::withTrashed()->with(['project', 'users'])->findOrFail($taskId);
    }

    private function canAccessTask(Task $task, string $permissionName): bool
    {
        $permission = user()->permission($permissionName);
        $userId = user()->id;
        $taskUsers = $task->users->pluck('id')->all();
        $project = $task->project;

        if ($permission === 'all') {
            return true;
        }

        if ($permission === 'added' && (int) $task->added_by === (int) $userId) {
            return true;
        }

        if ($permission === 'owned' && in_array($userId, $taskUsers)) {
            return true;
        }

        if ($permission === 'both' && (in_array($userId, $taskUsers) || (int) $task->added_by === (int) $userId)) {
            return true;
        }

        if ($project && (int) $project->project_admin === (int) $userId) {
            return true;
        }

        if ($project && in_array('client', user_roles()) && (int) $project->client_id === (int) $userId && in_array($permission, ['owned', 'both'])) {
            return true;
        }

        return false;
    }

    private function canViewProject(Project $project): bool
    {
        $permission = user()->permission('view_projects');
        $userId = user()->id;
        $memberIds = $project->members->pluck('user_id')->all();

        if ($permission === 'all' || (bool) $project->public) {
            return true;
        }

        if ($permission === 'added' && (int) $project->added_by === (int) $userId) {
            return true;
        }

        if ($permission === 'owned') {
            if (in_array('client', user_roles())) {
                return (int) $project->client_id === (int) $userId;
            }

            return in_array($userId, $memberIds);
        }

        if ($permission === 'both') {
            if (in_array('client', user_roles())) {
                return (int) $project->client_id === (int) $userId || (int) $project->added_by === (int) $userId;
            }

            return in_array($userId, $memberIds) || (int) $project->added_by === (int) $userId;
        }

        return false;
    }
}
