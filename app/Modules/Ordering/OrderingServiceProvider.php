<?php

namespace App\Modules\Ordering;

use App\Modules\ModuleServiceProvider;

final class OrderingServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        //
    }

    protected function moduleAlias(): string
    {
        return 'ordering';
    }
}