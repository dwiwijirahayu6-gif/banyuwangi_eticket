<?php

namespace App\Modules\Admin\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class AuditLogger
{
    public function record(string $entity, ?int $entityId, string $action, ?array $before = null, ?array $after = null, ?int $tenantId = null, ?int $canteenId = null): AuditLog
    {
        $userId = Auth::id();
        $payload = [
            'action' => $action,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('audit_logs', 'user_id')) {
            $payload['user_id'] = $userId;
        }
        if (Schema::hasColumn('audit_logs', 'actor_id')) {
            $payload['actor_id'] = $userId;
        }
        if (Schema::hasColumn('audit_logs', 'entity_type')) {
            $payload['entity_type'] = $entity;
        }
        if (Schema::hasColumn('audit_logs', 'entity')) {
            $payload['entity'] = $entity;
        }
        if (Schema::hasColumn('audit_logs', 'entity_id')) {
            $payload['entity_id'] = $entityId !== null ? (string) $entityId : null;
        }
        if (Schema::hasColumn('audit_logs', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('audit_logs', 'canteen_id')) {
            $payload['canteen_id'] = $canteenId;
        }
        if (Schema::hasColumn('audit_logs', 'ip_address')) {
            $payload['ip_address'] = request()->ip() ?? '127.0.0.1';
        }
        if (Schema::hasColumn('audit_logs', 'request_id')) {
            $payload['request_id'] = request()->header('X-Request-Id') ?? (string) Str::uuid();
        }
        if (Schema::hasColumn('audit_logs', 'before')) {
            $payload['before'] = $before !== null ? $this->sanitize($before) : null;
        }
        if (Schema::hasColumn('audit_logs', 'after')) {
            $payload['after'] = $after !== null ? $this->sanitize($after) : null;
        }
        if (Schema::hasColumn('audit_logs', 'logged_at')) {
            $payload['logged_at'] = now();
        }

        return AuditLog::create($payload);
    }

    private function sanitize(array $data): array
    {
        // Menghapus data sensitif sebelum masuk ke database/log
        foreach (['account_number_cipher', 'account_number', 'password', 'password_hash'] as $key) {
            unset($data[$key]);
        }

        return $data;
    }
}
