<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\Task;
use Closure;
use Illuminate\Http\Request;

class ValidatePublicProjectShareAccess
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $routeName = $route?->getName();

        if ($routeName === 'front.gantt') {
            $project = Project::where('hash', $route->parameter('hash'))->firstOrFail();
            abort_unless($project->public_gantt_chart === 'enable', 403);
            $request->session()->put($this->sessionKey('gantt', $project->id), true);
            return $next($request);
        }

        if ($routeName === 'front.gantt_data') {
            $project = Project::findOrFail($route->parameter('id'));
            abort_unless($project->public_gantt_chart === 'enable', 403);
            $this->requireSession($request, 'gantt', $project->id);
        }

        if ($routeName === 'front.taskboard') {
            $project = Project::where('hash', $route->parameter('hash'))->firstOrFail();
            abort_unless($project->public_taskboard === 'enable', 403);

            if (!$request->session()->get($this->sessionKey('taskboard', $project->id), false)) {
                abort_unless($request->hasValidSignature(), 403);
                $request->session()->put($this->sessionKey('taskboard', $project->id), true);
            }

            return $next($request);
        }

        if ($routeName === 'front.taskboard.load_more') {
            $project = Project::where('hash', $route->parameter('hash'))->firstOrFail();
            abort_unless($project->public_taskboard === 'enable', 403);
            $this->requireSession($request, 'taskboard', $project->id);
        }

        if ($routeName === 'front.task_detail') {
            $task = Task::with('project')->where('hash', $route->parameter('id'))->firstOrFail();
            abort_unless(!$task->is_private && $task->project && $task->project->public_taskboard === 'enable', 403);
            $this->requireSession($request, 'taskboard', $task->project_id);
        }

        return $next($request);
    }

    private function requireSession(Request $request, string $type, $projectId): void
    {
        abort_unless(
            $projectId && $request->session()->get($this->sessionKey($type, $projectId), false),
            403
        );
    }

    private function sessionKey(string $type, $projectId): string
    {
        return 'public_project_share.' . $type . '.' . $projectId;
    }
}
