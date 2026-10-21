<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTenantRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'role',
    ];
}