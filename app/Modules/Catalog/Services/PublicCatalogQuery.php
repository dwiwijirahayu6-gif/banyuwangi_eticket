<?php

namespace App\Modules\Catalog\Services;

use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Query lintas-tenant yang SAH untuk katalog publik wisata Banyuwangi.
 * Bypass global scope dilakukan secara eksplisit lalu diganti filter pengganti:
 * hanya tenant berstatus aktif, destinasi terkait, dan tiket sellable/tersedia.
 * Eager load tenant & destination untuk mencegah masalah query N+1.
 */
final class PublicCatalogQuery
{
    /**
     * Mengambil tiket untuk satu destinasi tertentu.
     *
     * @return Collection<int, TicketType>
     */
    public function forDestination(Destination $destination): Collection
    {
        return TicketType::query()
            ->withoutGlobalScope('tenant')
            ->where('destination_id', $destination->id)
            ->where('is_available', true)
            ->whereHas('tenant', fn ($query) => $query->where('status', 'active'))
            ->orderBy('name')
            ->get();
    }

    /**
     * Katalog publik paginated + searchable + filter tenant/destinasi.
     * Eager load tenant/destination untuk anti N+1 query.
     *
     * @return LengthAwarePaginator<int, TicketType>
     */
    public function browse(?Destination $destination = null, string $search = '', ?int $tenantId = null): LengthAwarePaginator
    {
        return TicketType::query()
            ->withoutGlobalScope('tenant')
            ->sellable()
            ->whereHas('tenant', fn ($query) => $query->where('status', 'active'))
            ->when($destination !== null, fn ($query) => $query->where('destination_id', $destination->id))
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->with(['tenant:id,name,code', 'destination:id,name,slug,operating_hours'])
            ->orderBy('name')
            ->paginate(16)
            ->withQueryString();
    }

    /**
     * UC-01: Seluruh tiket (termasuk yang habis/kuota nol, alur 4a) milik tenant aktif,
     * dengan pencarian nama tiket/destinasi/kategori serta saringan tenant dan kategori.
     *
     * @return Collection<int, TicketType>
     */
    public function browseAll(?Destination $destination = null, string $search = '', ?int $tenantId = null, ?string $category = null): Collection
    {
        return TicketType::query()
            ->withoutGlobalScope('tenant')
            ->whereHas('tenant', fn ($query) => $query->where('status', 'active'))
            ->when($destination !== null, fn ($query) => $query->where('destination_id', $destination->id))
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($category !== null && $category !== '', fn ($query) => $query->where('category', $category))
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like): void {
                    $q->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('category', 'like', $like)
                        ->orWhereHas('destination', fn ($d) => $d->where('name', 'like', $like))
                        ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', $like));
                });
            })
            ->with(['tenant:id,name,code', 'destination:id,name,slug,operating_hours'])
            ->orderBy('destination_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Nama kategori tiket unik lintas tenant aktif (untuk chip saringan katalog).
     *
     * @return SupportCollection<int, string>
     */
    public function categoryNames(?Destination $destination = null): SupportCollection
    {
        return TicketType::query()->withoutGlobalScope('tenant')
            ->whereHas('tenant', fn ($query) => $query->where('status', 'active'))
            ->when($destination !== null, fn ($query) => $query->where('destination_id', $destination->id))
            ->whereNotNull('category')
            ->orderBy('category')
            ->distinct()
            ->pluck('category');
    }

    /**
     * Daftar tenant aktif.
     *
     * @return SupportCollection<int, Tenant>
     */
    public function activeTenants(): SupportCollection
    {
        return Tenant::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Daftar destinasi aktif.
     *
     * @return SupportCollection<int, Destination>
     */
    public function activeDestinations(): SupportCollection
    {
        return Destination::query()
            ->whereHas('tenant', fn ($query) => $query->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'operating_hours', 'tenant_id']);
    }
}
