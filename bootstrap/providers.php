<?php

use App\Modules\Admin\AdminServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Ordering\OrderingServiceProvider;
use App\Modules\Payments\PaymentsServiceProvider;
use App\Modules\Reporting\ReportingServiceProvider;
use App\Modules\Ticketing\TicketingServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    // Modular monolith – satu provider per bounded context (Modul 2).
    AdminServiceProvider::class,
    CatalogServiceProvider::class,
    OrderingServiceProvider::class,
    PaymentsServiceProvider::class,
    TicketingServiceProvider::class,
    ReportingServiceProvider::class,
];