<?php

namespace App\Http\Middleware;

use App\Helper\UserService;
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
            'tasks.show',
            'tasks.reminder',
            'tasks.check_task',
            'tasks.members',
        ];

        $editRoutes = [
            'tasks.gantt_task_update',
            'tasks.update',
        ];

        $statusRoutes = [
            'tasks.send_approval',
            'tasks.show_status_reason_modal',
            'tasks.store_comment_on_change_status',
        ];

        // Duplicating a task must not be a way to read/copy an inaccessible task.
        if ($request->routeIs('tasks.create') && $request->filled('duplicate_task')) {
            $source = $this->findTask($request->input('duplicate_task'));
            abort_403(!$this->canViewTask($source));
        }

        if ($request->routeIs('tasks.store')) {
            if ($request->filled('taskId')) {
                $source = $this->findTask($request->input('taskId'));
                abort_403(!$this->canViewTask($source));
            }

            $this->authorizeTaskTargetProject($request->input('project_id'));
        }

        if ($request->routeIs(...$viewRoutes)) {
            $task = $this->resolveTask($request);
            abort_403(!$this->canViewTask($task));
        }

        if ($request->routeIs(...$editRoutes)) {
            $task = $this->resolveTask($request);
            abort_403(!$this->canEditTask($task));

            if ($request->routeIs('tasks.update')) {
                $this->authorizeTaskTargetProject($request->input('project_id'), $task);
            }
        }

        if ($request->routeIs(...$statusRoutes)) {
            $task = $this->resolveTask($request);
            abort_403(!$this->canChangeStatus($task));
        }

        if ($request->routeIs('tasks.clientDetail')) {
            $projectId = $request->input('id');
            abort_403(empty($projectId));
            $project = Project::with('members')->findOrFail($projectId);
            abort_403(!$this->canViewProject($project));
        }

        if ($request->routeIs('tasks.project_tasks')) {
            $projectId = $request->route('id');

            // id=0 historically returned tasks from every project. Keep that only
            // for users with global task visibility; otherwise it leaks task data.
            if (!$projectId || (int) $projectId === 0) {
                abort_403(user()->permission('view_tasks') !== 'all');
            } else {
                $project = Project::with('members')->findOrFail($projectId);
                abort_403(!$this->canViewProject($project));
                abort_403(user()->permission('view_tasks') === 'none');
            }
        }

        return $next($request);
    }

    private function authorizeTaskTargetProject($projectId, ?Task $task = null): void
    {
        if ($projectId === null || $projectId === '' || $projectId === 'all') {
            abort_403(!in_array(user()->permission('add_tasks'), ['all', 'added'], true) && !$task);
            return;
        }

        $project = Project::with('members')->findOrFail($projectId);
        $ids = $this->identities();
        $isProjectAdmin = in_array((int) $project->project_admin, $ids, true);

        // A user may work with a project only if it is actually visible to them.
        // Project admins are an explicit exception already used by TaskController.
        abort_403(!$isProjectAdmin && !$this->canViewProject($project));

        if (!$task) {
            abort_403(!$isProjectAdmin && !in_array(user()->permission('add_tasks'), ['all', 'added'], true));
        }
    }

    private function resolveTask(Request $request): Task
    {
        $taskId = $request->route('taskID')
            ?? $request->route('task')
            ?? $request->route('id')
            ?? $request->input('taskId')
            ?? $request->input('id');

        abort_403(empty($taskId));

        return $this->findTask($taskId);
    }

    private function findTask($taskId): Task
    {
        return Task::withTrashed()->with(['project', 'users', 'mentionTask'])->findOrFail($taskId);
    }

    private function identities(): array
    {
        return array_values(array_unique(array_filter([
            (int) user()->id,
            (int) UserService::getUserId(),
        ])));
    }

    private function taskFlags(Task $task): array
    {
        $ids = $this->identities();
        $taskUsers = $task->users->pluck('id')->map(fn ($id) => (int) $id)->all();
        $project = $task->project;
        $isClient = in_array('client', user_roles(), true);

        return [
            'ids' => $ids,
            'assigned' => count(array_intersect($ids, $taskUsers)) > 0,
            'unassigned' => $taskUsers === [],
            'added' => in_array((int) $task->added_by, $ids, true),
            'clientOwner' => $isClient && $project && in_array((int) $project->client_id, $ids, true),
            'projectAdmin' => $project && in_array((int) $project->project_admin, $ids, true),
            'mentioned' => $task->mentionTask->pluck('user_id')->map(fn ($id) => (int) $id)->intersect($ids)->isNotEmpty(),
        ];
    }

    private function canViewTask(Task $task): bool
    {
        $permission = user()->permission('view_tasks');
        $flags = $this->taskFlags($task);
        $canViewUnassigned = in_array('employee', user_roles(), true)
            && $flags['unassigned']
            && user()->permission('view_unassigned_tasks') === 'all';

        return $permission === 'all'
            || ($permission === 'added' && $flags['added'])
            || ($permission === 'owned' && ($flags['assigned'] || $flags['clientOwner']))
            || ($permission === 'both' && ($flags['assigned'] || $flags['added'] || $flags['clientOwner']))
            || $flags['projectAdmin']
            || $flags['mentioned']
            || $canViewUnassigned;
    }

    private function canEditTask(Task $task): bool
    {
        $permission = user()->permission('edit_tasks');
        $flags = $this->taskFlags($task);

        return $permission === 'all'
            || ($permission === 'added' && $flags['added'])
            || ($permission === 'owned' && ($flags['assigned'] || $flags['clientOwner']))
            || ($permission === 'both' && ($flags['assigned'] || $flags['added'] || $flags['clientOwner']))
            || $flags['projectAdmin'];
    }

    private function canChangeStatus(Task $task): bool
    {
        $permission = user()->permission('change_status');
        $flags = $this->taskFlags($task);

        // Mirror TaskController::changeStatus: being a project client alone does
        // not grant status-changing rights unless the task is assigned/added.
        return $permission === 'all'
            || ($permission === 'added' && $flags['added'])
            || ($permission === 'owned' && $flags['assigned'])
            || ($permission === 'both' && ($flags['assigned'] || $flags['added']))
            || $flags['projectAdmin'];
    }

    private function canViewProject(Project $project): bool
    {
        $permission = user()->permission('view_projects');
        $ids = $this->identities();
        $isClient = in_array('client', user_roles(), true);
        $isEmployee = in_array('employee', user_roles(), true);
        $memberIds = $project->members->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $isAdded = in_array((int) $project->added_by, $ids, true);
        $isClientOwner = $isClient && in_array((int) $project->client_id, $ids, true);
        $isMember = $isEmployee && count(array_intersect($ids, $memberIds)) > 0;

        if ($permission === 'all') {
            return true;
        }

        if ((bool) $project->public && $permission !== 'none') {
            return true;
        }

        return ($permission === 'added' && $isAdded)
            || ($permission === 'owned' && ($isClientOwner || $isMember))
            || ($permission === 'both' && ($isAdded || $isClientOwner || $isMember));
    }
}
