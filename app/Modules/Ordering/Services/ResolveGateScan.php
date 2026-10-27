<?php

namespace App\Modules\Ordering\Services;

use App\Models\CustomerSession;
use App\Models\GateQrToken;
use App\Support\Tokens\OpaqueToken;

final class ResolveGateScan
{
    public function resolve(string $plainToken): ?array
    {
        // 1. Cari token berdasarkan hash-nya
        $qr = GateQrToken::query()
            ->where('token_hash', OpaqueToken::hash($plainToken))
            ->with('gate.destination')
            ->first();

        // 2. Validasi token aktif
        if ($qr === null || ! $qr->isActive()) {
            return null;
        }

        $gate = $qr->gate;
        
        // 3. Validasi gerbang aktif
        if ($gate === null || ! $gate->isActive()) {
            return null;
        }

        $destination = $gate->destination;
        if (!$destination) {
            return null;
        }

        // 4. Buat Sesi Pelanggan (Customer Session)
        $sessionToken = OpaqueToken::issue(32);
        $session = new CustomerSession();
        $session->destination_id = $destination->id;
        $session->gate_id = $gate->id;
        $session->qr_token_id = $qr->id;
        $session->session_token_hash = $sessionToken['hash'];
        $session->status = 'active';
        $session->expires_at = now()->addHours(4); // Berlaku 4 jam
        $session->save();

        return [
            'session' => $session,
            'plain' => $sessionToken['plain'],
            'destination' => $destination
        ];
    }
}