<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AccountSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $global = global_setting();

        $rules = [
            'company_name' => 'required',
            'full_name' => 'required',
            'email' => 'required|email:rfc,strict',
            'password' => 'required|min:8',
        ];

        if ($global && $global->sign_up_terms == 'yes') {
            $rules['terms_and_conditions'] = 'required';
        }

        return $rules;
    }
    public function messages()
    {
        return [
            'company_name.required' => 'لطفاً نام شرکت را وارد کنید.',
            'email.required' => 'ایمیل نمی‌تواند خالی باشد.',
            'email.email' => 'لطفاً یک ایمیل معتبر وارد کنید.',
            'password.required' => 'رمز عبور نمی‌تواند خالی باشد.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'terms_and_conditions.required' => 'قبول شرایط و قوانین الزامی است.',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (User::where('email', $this->email)->exists()) {
                $validator->errors()->add('email', 'این ایمیل قبلاً ثبت شده است.');
            }
        });
    }

}
