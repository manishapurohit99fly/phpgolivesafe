<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends BaseAdminUserRequest
{
    /**
     * Module-specific rules for updating an admin user.
     * These are merged on top of the common rules in BaseAdminUserRequest.
     *
     * @return array<string, mixed>
     */
    protected function moduleRules(): array
    {
        $usersTable = config('tables.users');
        $userId     = $this->route('userId');

        return [
            'email'    => [
                'required',
                'email',
                'max:255',
                Rule::unique($usersTable, 'email')->ignore($userId),
            ],
            'phone_no' => [
                'required',                
                Rule::unique($usersTable, 'phone_no')->ignore($userId),
            ],            
        ];
    }
}
