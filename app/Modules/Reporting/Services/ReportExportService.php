<?php

namespace App\Modules\Reporting\Services;

use App\Models\Destination;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Ekspor Laporan ke format CSV / Excel compatible.
     */
    public function exportExcel(Tenant $tenant, ?Destination $destination, array $report): StreamedResponse
    {
        $destName = $destination?->name ?? $tenant->display_name;
        $filename = 'Laporan-Penjualan-'.str_replace(' ', '-', $tenant->code).'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tenant, $destName, $report) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar Excel membaca karakter dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            // Header informasi
            fputcsv($handle, ['LAPORAN OPERASIONAL & PENJUALAN DESTINASI WISATA BANYUWANGI']);
            fputcsv($handle, ['Nama Pengelola / Tenant', $tenant->name]);
            fputcsv($handle, ['Destinasi Wisata', $destName]);
            fputcsv($handle, ['Periode Laporan', $report['period_label']]);
            fputcsv($handle, ['Tanggal Pembuatan Laporan', now()->translatedFormat('d F Y, H:i').' WIB']);
            fputcsv($handle, []);

            // Ringkasan
            fputcsv($handle, ['RINGKASAN EKSEKUTIF']);
            fputcsv($handle, ['Indikator', 'Nilai']);
            fputcsv($handle, ['Total Pemesanan (Booking)', $report['total_bookings']]);
            fputcsv($handle, ['Pemesanan Lunas', $report['total_paid_bookings']]);
            fputcsv($handle, ['Total Tiket Terjual', $report['total_tickets_sold']]);
            fputcsv($handle, ['Total Pengunjung (Check-in)', $report['total_visitors']]);
            fputcsv($handle, ['Total Pendapatan Kotor', 'Rp '.number_format($report['gross_revenue'], 0, ',', '.')]);
            fputcsv($handle, ['Komisi Platform', 'Rp '.number_format($report['platform_commission'], 0, ',', '.')]);
            fputcsv($handle, ['Pendapatan Bersih Tenant', 'Rp '.number_format($report['net_revenue'], 0, ',', '.')]);
            fputcsv($handle, ['Saldo Tersedia', 'Rp '.number_format($report['available_balance'], 0, ',', '.')]);
            fputcsv($handle, []);

            // Rincian harian
            fputcsv($handle, ['RINCIAN TRANSAKSI HARIAN']);
            fputcsv($handle, ['Tanggal', 'Jumlah Booking', 'Tiket Terjual', 'Pengunjung (Check-in)', 'Pendapatan Kotor (Rp)', 'Komisi Platform (Rp)', 'Pendapatan Bersih (Rp)']);

            foreach ($report['daily_summary'] as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['booking_count'],
                    $row['tickets_sold'],
                    $row['visitors'],
                    $row['gross_revenue'],
                    $row['platform_commission'],
                    $row['net_revenue'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Ekspor Laporan ke format berkas PDF standar murni (valid PDF-1.4).
     */
    public function exportPdf(Tenant $tenant, ?Destination $destination, array $report): StreamedResponse
    {
        $destName = $destination?->name ?? $tenant->display_name;
        $filename = 'Laporan-Penjualan-'.str_replace(' ', '-', $tenant->code).'-'.now()->format('Ymd-His').'.pdf';

        $pdfContent = $this->generatePdfDocument($tenant, $destName, $report);

        return response()->stream(function () use ($pdfContent) {
            echo $pdfContent;
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($pdfContent),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Membangun representasi binary PDF-1.4 standar tanpa dependensi eksternal.
     */
    private function generatePdfDocument(Tenant $tenant, string $destName, array $report): string
    {
        $lines = [];
        $lines[] = 'LAPORAN OPERASIONAL & PENJUALAN TIKET';
        $lines[] = 'SISTEM E-TICKET KABUPATEN BANYUWANGI';
        $lines[] = '------------------------------------------------------------';
        $lines[] = 'Pengelola (Tenant)    : '.$this->sanitizeText($tenant->name);
        $lines[] = 'Destinasi Wisata      : '.$this->sanitizeText($destName);
        $lines[] = 'Periode Laporan       : '.$this->sanitizeText($report['period_label']);
        $lines[] = 'Waktu Dibuat          : '.now()->format('d/m/Y H:i').' WIB';
        $lines[] = '------------------------------------------------------------';
        $lines[] = 'RINGKASAN:';
        $lines[] = '  - Total Booking     : '.$report['total_bookings'];
        $lines[] = '  - Booking Lunas     : '.$report['total_paid_bookings'];
        $lines[] = '  - Tiket Terjual     : '.$report['total_tickets_sold'].' tiket';
        $lines[] = '  - Pengunjung Masuk  : '.$report['total_visitors'].' orang';
        $lines[] = '  - Pendapatan Kotor  : Rp '.number_format($report['gross_revenue'], 0, ',', '.');
        $lines[] = '  - Komisi Platform   : Rp '.number_format($report['platform_commission'], 0, ',', '.');
        $lines[] = '  - Pendapatan Bersih : Rp '.number_format($report['net_revenue'], 0, ',', '.');
        $lines[] = '  - Saldo Tersedia    : Rp '.number_format($report['available_balance'], 0, ',', '.');
        $lines[] = '------------------------------------------------------------';
        $lines[] = 'RINCIAN TRANSAKSI HARIAN:';
        $lines[] = sprintf('%-12s | %-7s | %-7s | %-15s | %-15s', 'Tanggal', 'Tiket', 'Tamu', 'Kotor (Rp)', 'Bersih (Rp)');
        $lines[] = '------------------------------------------------------------';

        $count = 0;
        foreach ($report['daily_summary'] as $row) {
            if ($count++ >= 25) {
                $lines[] = '... (dan transaksi lainnya)';
                break;
            }
            $lines[] = sprintf(
                '%-12s | %-7d | %-7d | %-15s | %-15s',
                $row['date'],
                $row['tickets_sold'],
                $row['visitors'],
                number_format($row['gross_revenue'], 0, ',', '.'),
                number_format($row['net_revenue'], 0, ',', '.')
            );
        }

        $lines[] = '------------------------------------------------------------';
        $lines[] = 'Dokumen resmi ini dicetak secara otomatis oleh Banyuwangi E-Ticket.';

        // Buat stream teks PDF
        $stream = "BT\n/F1 10 Tf\n14 TL\n40 790 Td\n";
        foreach ($lines as $line) {
            $stream .= '('.$this->escapePdfString($line).") Tj T*\n";
        }
        $stream .= "ET\n";

        $streamLen = strlen($stream);

        // Objek PDF standar
        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>";
        $objects[5] = "<< /Length {$streamLen} >>\nstream\n{$stream}endstream";

        $output = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $id => $obj) {
            $offsets[$id] = strlen($output);
            $output .= "{$id} 0 obj\n{$obj}\nendobj\n";
        }

        $xrefOffset = strlen($output);
        $output .= "xref\n0 ".(count($objects) + 1)."\n";
        $output .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $output .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $output .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $output .= "startxref\n{$xrefOffset}\n%%EOF";

        return $output;
    }

    private function escapePdfString(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function sanitizeText(string $text): string
    {
        return preg_replace('/[^\x20-\x7E]/', '', $text);
    }
}
