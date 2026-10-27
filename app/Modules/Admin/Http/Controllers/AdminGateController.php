<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Gate;
use App\Modules\Admin\Services\QrTokenService;
use App\Modules\Admin\Services\QrCodeSvg; // Tambahan Import
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminGateController extends Controller
{
    use AuthorizesRequests;

    // Tambahkan QrCodeSvg di construct
    public function __construct(
        private QrTokenService $qr, 
        private QrCodeSvg $svg
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Gate::class);
        
        // Ambil semua gerbang dari semua destinasi
        $gates = Gate::with('destination')->orderBy('id', 'desc')->get();
        $destinations = Destination::all();

        return view('admin::gates.index', compact('gates', 'destinations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Gate::class);

        $data = $request->validate([
            'destination_id' => ['required', 'exists:destinations,id'],
            'code' => ['required', 'alpha_dash', 'max:30'],
            'label' => ['required', 'string', 'max:60'],
            'zone' => ['nullable', 'string', 'max:60'],
        ]);

        (new Gate)->forceFill([
            'destination_id' => $data['destination_id'],
            'code' => $data['code'],
            'label' => $data['label'],
            'zone' => $data['zone'] ?? null,
            'status' => 'active',
        ])->save();

        return redirect()->route('admin.gates.index')->with('status', 'Gerbang berhasil dibuat.');
    }

    public function rotate(Gate $gate): RedirectResponse
    {
        $this->authorize('rotateQr', $gate);
        $plain = $this->qr->rotate($gate);
        
        return redirect()->route('admin.gates.qr', $gate)->with('qr_plain', $plain);
    }

    public function qr(Gate $gate): View
    {
        $this->authorize('rotateQr', $gate);
        $plain = session('qr_plain');
        $url = is_string($plain) ? url('/q/' . $plain) : null;
        
        // Proses URL menjadi gambar SVG QR Code
        $svg = $url !== null ? $this->svg->render($url) : null;

        return view('admin::gates.qr', compact('gate', 'plain', 'url', 'svg'));
    }
}