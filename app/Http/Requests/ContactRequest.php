<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contactId = $this->route('contact') ? $this->route('contact')->id : null;
        $isEdit = $this->isMethod('PUT') || $contactId;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('contacts')->ignore($contactId)
            ],
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
            'profile_image' => $isEdit ? 'nullable|image|mimes:jpeg,png,jpg|max:2048' : 'required|image|mimes:jpeg,png,jpg|max:2048',
            'additional_file' => $isEdit ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
            'custom_fields' => $isEdit ? 'nullable|array|min:1' : 'required|array|min:1',
            'custom_fields.*' => 'required|string|max:500'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $customFields = $this->input('custom_fields', []);
            
            if (!empty($customFields)) {
                foreach ($customFields as $key => $value) {
                    if (empty($key) || empty($value)) {
                        $validator->errors()->add('custom_fields', 'Both field name and field value are required for all custom fields');
                        break;
                    }
                }
            } elseif (!$this->isMethod('PUT')) {
                $validator->errors()->add('custom_fields', 'At least one custom field is required');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email',
            'email.unique' => 'This email is already taken',
            'phone.required' => 'Phone is required',
            'gender.required' => 'Gender is required',
            'profile_image.required' => 'Profile image is required',
            'profile_image.mimes' => 'Profile image must be PNG, JPG, or JPEG format',
            'additional_file.required' => 'Additional file is required',
            'additional_file.mimes' => 'Additional file must be PDF format',
            'custom_fields.required' => 'At least one custom field is required',
            'custom_fields.*.required' => 'Custom field value is required'
        ];
    }
}
