<?php

namespace App\Modules\Ticketing;

use App\Modules\ModuleServiceProvider;

final class TicketingServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        //
    }

    protected function moduleAlias(): string
    {
        return 'ticketing';
    }
}