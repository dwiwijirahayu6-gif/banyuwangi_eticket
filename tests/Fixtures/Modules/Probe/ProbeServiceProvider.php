<?php

namespace Tests\Fixtures\Modules\Probe;

use App\Modules\ModuleServiceProvider;

final class ProbeServiceProvider extends ModuleServiceProvider
{
    protected function moduleAlias(): string
    {
        return 'probe';
    }
}
