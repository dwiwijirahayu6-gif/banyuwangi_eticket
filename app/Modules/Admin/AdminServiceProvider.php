<?php

namespace App\Modules\Admin;

use App\Modules\ModuleServiceProvider;

final class AdminServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        //
    }

    protected function moduleAlias(): string
    {
        return 'admin';
    }
}