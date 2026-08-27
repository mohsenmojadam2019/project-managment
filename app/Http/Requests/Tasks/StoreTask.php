<?php

namespace App\Http\Requests\Tasks;

use App\Http\Requests\CoreRequest;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Traits\CustomFieldsRequestTrait;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class StoreTask extends CoreRequest
{
    use CustomFieldsRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $setting = company();
        $companyId = $setting->id;
        $projectId = $this->filled('project_id') && $this->project_id !== 'all'
            ? (int) $this->project_id
            : null;

        $project = $projectId
            ? Project::where('company_id', $companyId)->find($projectId)
            : null;

        $milestone = $this->filled('milestone_id') && $projectId
            ? ProjectMilestone::where('project_id', $projectId)->find($this->milestone_id)
            : null;

        $milestoneEndDate = $milestone?->end_date ? Carbon::parse($milestone->end_date) : null;
        $waitingApproval = TaskboardColumn::waitingForApprovalColumn();
        $unassignedPermission = user()->permission('create_unassigned_tasks');

        $rules = [
            'heading' => ['required'],
            'start_date' => ['required', 'date_format:' . $setting->date_format],
            'priority' => ['required'],
            'project_id' => [
                'nullable',
                'integer',
                Rule::exists('projects', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'board_column_id' => [
                'nullable',
                'integer',
                Rule::exists('taskboard_columns', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'milestone_id' => ['nullable', 'integer'],
            'dependent_task_id' => ['required_with:dependent', 'nullable', 'integer'],
        ];

        if ($waitingApproval) {
            $rules['board_column_id'][] = Rule::notIn([$waitingApproval->id]);
        }

        if ($project && $project->need_approval_by_admin != 0 && $waitingApproval) {
            // Waiting approval is allowed only for projects configured for approval.
            $rules['board_column_id'] = array_values(array_filter(
                $rules['board_column_id'],
                fn ($rule) => !($rule instanceof \Illuminate\Validation\Rules\NotIn)
            ));
        }

        if (in_array('client', user_roles(), true)) {
            array_unshift($rules['project_id'], 'required');
        }

        if ($projectId) {
            $rules['milestone_id'][] = Rule::exists('project_milestones', 'id')
                ->where(fn ($query) => $query->where('project_id', $projectId));
        } else {
            $rules['milestone_id'][] = 'prohibited';
        }

        $rules['dependent_task_id'][] = Rule::exists('tasks', 'id')->where(function ($query) use ($companyId, $projectId) {
            $query->where('company_id', $companyId)->whereNull('deleted_at');

            if ($projectId) {
                $query->where('project_id', $projectId);
            } else {
                $query->whereNull('project_id');
            }
        });

        if (!$this->has('without_duedate')) {
            $rules['due_date'] = ['required', 'date_format:' . $setting->date_format, 'after_or_equal:start_date'];

            if ($milestoneEndDate) {
                $rules['due_date'][] = 'before_or_equal:' . $milestoneEndDate->format($setting->date_format);
            }
        }

        if ($project?->start_date) {
            $rules['start_date'][] = 'after_or_equal:' . $project->start_date->format($setting->date_format);
        }

        if ($this->has('dependent') && $this->filled('dependent_task_id')) {
            $dependentTask = Task::where('company_id', $companyId)
                ->whereKey($this->dependent_task_id)
                ->first();

            if ($dependentTask?->due_date) {
                $rules['start_date'][] = 'after_or_equal:' . $dependentTask->due_date->format($setting->date_format);
            }
        }

        $rules['user_id.0'] = $unassignedPermission === 'all' ? 'required_with:is_private' : 'required';

        if ($this->has('repeat')) {
            $rules['repeat_cycles'] = 'required|numeric|min:1';
            $rules['repeat_count'] = 'required|numeric|min:1';
        }

        if ($this->has('set_time_estimate')) {
            $rules['estimate_hours'] = 'required|integer|min:0';
            $rules['estimate_minutes'] = 'required|integer|min:0|max:59';
        }

        return $this->customFieldRules($rules);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('dependent') && $this->filled('dependent_task_id')) {
                $dependentTask = Task::where('company_id', company()->id)
                    ->whereKey($this->dependent_task_id)
                    ->first();

                if ($dependentTask && is_null($dependentTask->due_date)) {
                    $validator->errors()->add('dependent_task_id', __('messages.taskDependentDate'));
                }
            }
        });
    }

    public function messages()
    {
        $project = $this->filled('project_id')
            ? Project::where('company_id', company()->id)->find($this->project_id)
            : null;

        return [
            'project_id.required' => __('messages.chooseProject'),
            'due_date.after_or_equal' => __('messages.taskAfterDateValidation'),
            'due_date.before_or_equal' => __('messages.taskBeforeDateValidation'),
            'board_column_id.not_in' => $project ? __('messages.selectStatus') : __('messages.selectAnotherStatus'),
        ];
    }

    public function attributes()
    {
        return $this->customFieldsAttributes([
            'user_id.0' => __('modules.tasks.assignTo'),
            'dependent_task_id' => __('modules.tasks.dependentTask'),
            'board_column_id' => __('modules.tasks.status'),
        ]);
    }
}
