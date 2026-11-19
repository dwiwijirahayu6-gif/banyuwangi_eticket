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

    /**
     * Menyiapkan dan menyelaraskan input form baru maupun backward-compatibility legacy.
     */
    protected function prepareForValidation(): void
    {
        $merges = [];

        if ($this->filled('name') && ! $this->filled('destination_name')) {
            $merges['destination_name'] = $this->input('name');
        }

        if ($this->filled('pic_name') && ! $this->filled('admin_name')) {
            $merges['admin_name'] = $this->input('pic_name');
        }

        if ($this->filled('pic_email') && ! $this->filled('admin_email')) {
            $merges['admin_email'] = $this->input('pic_email');
        }

        if (! $this->filled('location')) {
            $merges['location'] = 'Banyuwangi';
        }

        if (! $this->filled('description')) {
            $merges['description'] = 'Destinasi wisata di Banyuwangi.';
        }

        if (! $this->filled('admin_password')) {
            $merges['admin_password'] = 'PasswordAman123!';
        }

        if (! empty($merges)) {
            $this->merge($merges);
        }
    }

    public function rules(): array
    {
        return [
            // Bagian 1: Informasi Destinasi Wisata
            'destination_name'  => ['required', 'string', 'max:255'],
            'code'              => ['nullable', 'string', 'alpha_dash', 'max:50', Rule::unique('tenants', 'code')],
            'location'          => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string'],
            'operating_hours'   => ['nullable', 'string', 'max:255'],
            'facilities'        => ['nullable', 'string'],
            'safety_guidelines' => ['nullable', 'string'],
            'image'             => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],

            // Bagian 2: Informasi Pengelola / Tenant Admin
            'admin_name'        => ['required', 'string', 'max:255'],
            'admin_email'       => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'admin_phone'       => ['nullable', 'string', 'max:50'],
            'admin_password'    => ['required', 'string', 'min:8'],

            // Bagian 3: Pengaturan Platform
            'commission_rate'   => ['nullable', 'numeric', 'between:0,100'],
            'status'            => ['nullable', 'in:active,inactive'],

            // Rekening Bank (Opsional)
            'bank_code'         => ['nullable', 'string', 'max:20'],
            'account_holder'    => ['nullable', 'string', 'max:120'],
            'account_number'    => ['nullable', 'digits_between:6,20'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_name.required' => 'Nama destinasi wisata wajib diisi.',
            'location.required'         => 'Lokasi / alamat wisata wajib diisi.',
            'description.required'      => 'Deskripsi wisata wajib diisi.',
            'admin_name.required'       => 'Nama pengelola / admin tenant wajib diisi.',
            'admin_email.required'      => 'Email admin tenant wajib diisi.',
            'admin_email.unique'        => 'Email tersebut sudah terdaftar untuk pengguna lain.',
            'admin_password.required'   => 'Password awal admin tenant wajib diisi.',
            'admin_password.min'        => 'Password awal minimal harus 8 karakter.',
            'commission_rate.between'   => 'Nilai komisi harus berada di antara 0% hingga 100%.',
            'image.max'                 => 'Ukuran foto maksimal adalah 2MB.',
        ];
    }
}
