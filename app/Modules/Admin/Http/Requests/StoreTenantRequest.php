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
            'name' => ['required', 'string', 'max:120'], // Sesuaikan dengan nama kolom di tabel tenants
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('tenants', 'code')],
            'commission_rate' => ['required', 'numeric', 'between:0,100'],
        ];
    }
}