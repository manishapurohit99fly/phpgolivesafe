<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base form request that holds the validation rules shared by every
 * admin-user module (create, update, etc.).
 *
 * Child requests should:
 *  - implement `moduleRules()` to add or override module-specific rules.
 *  - optionally override `messages()` / `attributes()` to add their own
 *    on top of the common ones.
 */
abstract class BaseAdminUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules common to every admin-user module.
     *
     * @return array<string, array<int, string>>
     */
    protected function commonRules(): array
    {
        return [
            'first_name'    => ['required', 'string', 'min:3', 'max:255'],
            'last_name'     => ['nullable', 'string', 'min:3', 'max:255'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'status'        => ['required', 'in:0,1'],
        ];
    }

    /**
     * Module-specific validation rules. These are merged on top of the
     * common rules and can override any key from `commonRules()`.
     *
     * @return array<string, mixed>
     */
    abstract protected function moduleRules(): array;

    /**
     * Final rules sent to the validator.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->commonRules(), $this->moduleRules());
    }

    /**
     * Custom attribute names common to every admin-user module.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge([
            'first_name'    => 'first name',
            'last_name'     => 'last name',
            'email'         => 'email',
            'phone_no'      => 'phone number',
            'password'      => 'password',
            'profile_photo' => 'profile photo',
            'status'        => 'status',
        ], $this->moduleAttributes());
    }

    /**
     * Custom error messages common to every admin-user module.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge([
            'first_name.required'    => 'First name is required.',
            'first_name.string'      => 'First name must be a string.',
            'first_name.min'         => 'First name must be at least 3 characters.',
            'first_name.max'         => 'First name may not exceed 255 characters.',            
            'last_name.string'       => 'Last name must be a string.',
            'last_name.min'          => 'Last name must be at least 3 characters.',
            'last_name.max'          => 'Last name may not exceed 255 characters.',            
            'profile_photo.mimes'     => 'Profile photo must be a JPG, JPEG or PNG file.',
            'profile_photo.max'       => 'Profile photo size may not exceed 2MB.',
        ], $this->moduleMessages());
    }

    /**
     * Override in a child request to add module-specific attribute names.
     *
     * @return array<string, string>
     */
    protected function moduleAttributes(): array
    {
        return [];
    }

    /**
     * Override in a child request to add module-specific error messages.
     *
     * @return array<string, string>
     */
    protected function moduleMessages(): array
    {
        return [];
    }
}
