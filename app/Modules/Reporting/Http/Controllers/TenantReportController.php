<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Reporting\Services\ReportExportService;
use App\Modules\Reporting\Services\TenantReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantReportController extends Controller
{
    public function __construct(
        private TenantReportService $reportService,
        private ReportExportService $exportService,
    ) {}

    public function index(Request $request, Tenant $tenant): View
    {
        $destination = $tenant->destinations()->first();
        $period = $request->get('period', 'this_month');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $report = $this->reportService->getReport($tenant->id, $period, $fromDate, $toDate);

        return view('reporting::tenant.reports.index', [
            'tenant' => $tenant,
            'destination' => $destination,
            'period' => $period,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'report' => $report,
        ]);
    }

    public function exportPdf(Request $request, Tenant $tenant): StreamedResponse
    {
        $destination = $tenant->destinations()->first();
        $period = $request->get('period', 'this_month');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $report = $this->reportService->getReport($tenant->id, $period, $fromDate, $toDate);

        return $this->exportService->exportPdf($tenant, $destination, $report);
    }

    public function exportExcel(Request $request, Tenant $tenant): StreamedResponse
    {
        $destination = $tenant->destinations()->first();
        $period = $request->get('period', 'this_month');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $report = $this->reportService->getReport($tenant->id, $period, $fromDate, $toDate);

        return $this->exportService->exportExcel($tenant, $destination, $report);
    }
}
