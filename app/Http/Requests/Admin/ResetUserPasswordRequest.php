<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResetUserPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id'               => ['required', 'integer', 'exists:' . config('tables.users') . ',id'],
            'password'              => ['required', 'string', 'min:6', 'max:15', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id'               => 'user',
            'password'              => 'new password',
            'password_confirmation' => 'confirm password',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists'           => 'The selected user does not exist.',
            'password.confirmed'       => 'New password and confirm password do not match.',
            'password_confirmation.required' => 'Please confirm the new password.',
        ];
    }
}
