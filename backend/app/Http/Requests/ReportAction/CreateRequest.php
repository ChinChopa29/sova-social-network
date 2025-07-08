<?php

namespace App\Http\Requests\ReportAction;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_id' => ['required', 'exists:reports,id'],
            'target_type' => ['required', 'string'],
            'target_id' => ['required', 'integer'],
            'target_user_id' => ['required', 'exists:users,id'],
            'action_type' => ['required', 'in:warning,ban,delete'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
