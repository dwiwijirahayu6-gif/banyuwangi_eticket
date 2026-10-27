<?php

namespace App\Modules\Admin\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Tenant::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('tenants', 'code')],
            'commission_rate' => ['required', 'numeric', 'between:0,100'],
            // UC-21: Penanggung Jawab
            'pic_name' => ['required', 'string', 'max:120'],
            'pic_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            // UC-21: Rekening Bank
            'bank_code' => ['required', 'string', 'max:20'],
            'account_holder' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'digits_between:6,20'],
        ];
    }
}