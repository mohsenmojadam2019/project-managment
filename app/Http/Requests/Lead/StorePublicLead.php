<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class StorePublicLead extends CoreRequest
{
    use CustomFieldsRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $companyId = (int) $this->input('company_id');

        $rules = [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required'],
            'email' => [
                'nullable',
                'email:rfc,strict',
                Rule::unique('leads', 'client_email')->where(fn ($query) => $query->where('company_id', $companyId)),
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('lead_categories', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'product' => ['nullable', 'array'],
            'product.*' => [
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];

        $rules = $this->customFieldRules($rules);

        if (global_setting()->google_recaptcha_status == 'active'
            && global_setting()->lead_form_google_captcha == 1
            && global_setting()->google_recaptcha_v2_status == 'active') {
            $rules['g-recaptcha-response'] = 'required';
        }

        return $rules;
    }

    public function attributes()
    {
        return $this->customFieldsAttributes([]);
    }
}
