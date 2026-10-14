<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_super_admin_holds_admin_role(): void
    {
        $u = new User(['name' => 'A', 'email' => 'a@b.c']);
        $u->role = 'super_admin';
        $u->status = 'active';

        $this->assertTrue($u->hasRole('admin'));
        $this->assertTrue($u->isAdmin());
        $this->assertFalse($u->hasRole('tenant'));
        $this->assertFalse($u->isTenantOperator());
    }

    public function test_tenant_admin_holds_tenant_role(): void
    {
        $u = new User;
        $u->role = 'tenant_admin';
        $u->status = 'active';

        $this->assertTrue($u->hasRole('tenant'));
        $this->assertTrue($u->isTenantOperator());
        $this->assertFalse($u->hasRole('admin'));
    }

    public function test_suspended_user_holds_no_role(): void
    {
        $u = new User;
        $u->role = 'super_admin';
        $u->status = 'suspended';

        $this->assertFalse($u->hasRole('admin'));
        $this->assertFalse($u->isActive());
    }
}