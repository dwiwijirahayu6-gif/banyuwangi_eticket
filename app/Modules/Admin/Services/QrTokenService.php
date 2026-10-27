<?php

namespace App\Modules\Admin\Services;

use App\Models\Gate;
use App\Models\GateQrToken;
use App\Support\Tokens\OpaqueToken;
use Illuminate\Support\Facades\DB;

final class QrTokenService
{
    public function __construct(private AuditLogger $audit) {}

    public function rotate(Gate $gate): string
    {
        return DB::transaction(function () use ($gate): string {
            $locked = Gate::query()->lockForUpdate()->findOrFail($gate->id);

            // Cabut token yang lama
            GateQrToken::query()
                ->where('gate_id', $locked->id)
                ->where('status', 'active')
                ->update(['status' => 'revoked', 'revoked_at' => now()]);

            // Buat token baru
            $token = OpaqueToken::issue(32);

            $qr = new GateQrToken();
            $qr->gate_id = $locked->id;
            $qr->token_hash = $token['hash'];
            $qr->status = 'active';
            $qr->issued_at = now();
            $qr->save();

            $this->audit->record('gate_qr_token', $qr->id, 'issued',
                null, ['gate_id' => $locked->id]);

            return $token['plain']; 
        });
    }
}