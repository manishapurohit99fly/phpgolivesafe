<?php

namespace App\Http\Requests\Admin;

class StoreAdminUserRequest extends BaseAdminUserRequest
{
    /**
     * Module-specific rules for creating an admin user.
     * These are merged on top of the common rules in BaseAdminUserRequest.
     *
     * @return array<string, mixed>
     */
    protected function moduleRules(): array
    {
        $usersTable = config('tables.users');

        return [
            'email'    => ['required', 'email', 'max:255', 'unique:' . $usersTable . ',email'],
            'phone_no' => ['required',  'unique:' . $usersTable . ',phone_no'],
        ];
    }
}
