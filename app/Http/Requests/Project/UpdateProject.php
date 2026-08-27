<?php

namespace App\Http\Requests\Project;

use App\Helper\UserService;
use App\Models\Project;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateProject extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!auth()->check()) {
            return false;
        }

        $projectId = $this->route('project') ?? $this->input('project_id');
        $project = Project::withTrashed()->find($projectId);

        if (!$project) {
            return false;
        }

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $projectId = $this->route('project') ?? $this->input('project_id');

        $rules = [
            'project_name' => 'required|max:150',
            'start_date' => 'required',
            'hours_allocated' => 'nullable|numeric',
            'client_id' => 'requiredIf:client_view_task,true',
            'project_code' => $this->project_code != '' ? 'unique:projects,project_short_code,' . $projectId . ',id,company_id,' . company()->id : '',
        ];

        if (!$this->has('without_deadline')) {
            $rules['deadline'] = 'required';
        }

        if ($this->project_budget != '') {
            $rules['project_budget'] = 'numeric';
            $rules['currency_id'] = 'required';
        }

        $project = Project::findOrFail($projectId);

        if (request()->private && in_array('employee', user_roles()))  {
            $rules['user_id.0'] = 'required';
        }

        if ($project->public == 0 && !request()->has('public')) {
            if (!request()->has('member_id') || (!request()->private && !request()->public)) {
                $rules['member_id.0'] = 'required';
            }
        }

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation'),
            'project_code.required' => __('messages.projectCodeRequired'),
            'member_id.0.required' => __('messages.atleastOneValidation')
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
