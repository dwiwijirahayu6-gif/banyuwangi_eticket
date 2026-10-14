<?php

namespace App\Modules\Reporting;

use App\Modules\ModuleServiceProvider;

final class ReportingServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        //
    }

    protected function moduleAlias(): string
    {
        return 'reporting';
    }
}