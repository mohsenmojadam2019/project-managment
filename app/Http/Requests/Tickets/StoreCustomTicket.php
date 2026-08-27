<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class StoreCustomTicket extends CoreRequest
{
    use CustomFieldsRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $setting = global_setting();
        $companyId = (int) $this->input('company_id');

        $rules = [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required'],
            'email' => ['required', 'email:rfc,strict'],
            'ticket_subject' => ['required'],
            'assign_group' => [
                'required',
                'integer',
                Rule::exists('ticket_groups', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'type' => [
                'nullable',
                'integer',
                Rule::exists('ticket_types', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'email_notifications' => ['nullable', 'boolean'],
            'message' => ['required', 'sometimes'],
            'ticket_description' => ['required', 'sometimes'],
        ];

        $rules = $this->customFieldRules($rules);

        if ($setting->google_recaptcha_status == 'active'
            && $setting->ticket_form_google_captcha == 1
            && $setting->google_recaptcha_v2_status == 'active') {
            $rules['g-recaptcha-response'] = 'required';
        }

        return $rules;
    }

    public function attributes()
    {
        return $this->customFieldsAttributes([]);
    }
}
