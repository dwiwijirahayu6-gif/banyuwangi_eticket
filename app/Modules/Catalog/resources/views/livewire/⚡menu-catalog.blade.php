<?php

use App\Models\Destination;
use App\Models\Tenant;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use App\Modules\Catalog\Services\WaitTimeEstimator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Katalog publik tiket wisata lintas tenant (UC-01) dengan estimasi waktu tunggu/pelayanan (UC-11).
 * Tiket habis tetap tampil nonaktif (alur 4a); destinasi tutup menampilkan jam buka (alur 4b).
 * Komponen memuat ulang tiap 5 detik (wire:poll.5s) sehingga sakelar ketersediaan (UC-14)
 * tampil <= 5 detik tanpa refresh manual.
 */
new class extends Component
{
    public string $canteenSlug = '';
    public string $destinationSlug = '';

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $tenantId = null;

    #[Url]
    public ?string $category = null;

    public function mount(string $canteenSlug = '', string $destinationSlug = ''): void
    {
        $this->canteenSlug = $canteenSlug ?: $destinationSlug;
        $this->destinationSlug = $this->canteenSlug;
    }

    public function filterCategory(?string $name): void
    {
        $this->category = $name;
    }

    #[Computed]
    public function destination(): ?Destination
    {
        if (empty($this->canteenSlug)) {
            return Destination::query()->first();
        }

        return Destination::query()
            ->where('slug', $this->canteenSlug)
            ->orWhereHas('tenant', fn ($t) => $t->where('code', $this->canteenSlug)->orWhere('status', 'active'))
            ->first();
    }

    #[Computed]
    public function canteen(): ?Destination
    {
        return $this->destination();
    }

    #[Computed]
    public function groups(): \Illuminate\Support\Collection
    {
        $query = app(PublicCatalogQuery::class);
        $tickets = $query->browseAll(null, $this->search, $this->tenantId, $this->category);

        return $tickets->groupBy(fn ($t) => $t->destination_id ?? $t->tenant_id);
    }

    #[Computed]
    public function tenants(): \Illuminate\Support\Collection
    {
        return app(PublicCatalogQuery::class)->activeTenants();
    }

    #[Computed]
    public function categories(): \Illuminate\Support\Collection
    {
        return app(PublicCatalogQuery::class)->categoryNames();
    }
};
?>

<div class="space-y-4" wire:poll.5s>
    @php($wait = app(WaitTimeEstimator::class))
    <div class="flex flex-col gap-2">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari tiket atau destinasi wisata…"
            class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800" />

        <select wire:model.live="tenantId" aria-label="Saring tenant"
            class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
            <option value="">Semua tenant / pengelola</option>
            @foreach ($this->tenants as $t)
                <option value="{{ $t->id }}" wire:key="tenant-{{ $t->id }}">{{ $t->display_name }}</option>
            @endforeach
        </select>

        <div class="flex gap-2 overflow-x-auto pb-1" data-test="category-chips">
            <button type="button" wire:click="filterCategory(null)"
                @class(['min-h-9 shrink-0 rounded-full border px-3 text-sm cursor-pointer', 'border-red-600 bg-red-600 text-white' => ! $category, 'border-zinc-300' => $category])>
                Semua
            </button>
            @foreach ($this->categories as $name)
                <button type="button" wire:key="chip-{{ $name }}" wire:click="filterCategory(@js($name))"
                    @class(['min-h-9 shrink-0 rounded-full border px-3 text-sm cursor-pointer', 'border-red-600 bg-red-600 text-white' => $category === $name, 'border-zinc-300' => $category !== $name])>
                    {{ $name }}
                </button>
            @endforeach
        </div>
    </div>

    @forelse ($this->groups as $tickets)
        @php($firstTicket = $tickets->first())
        @php($dest = $firstTicket->destination)
        @php($tenant = $firstTicket->tenant)
        @php($isOpen = $dest ? $dest->isOpen() : true)

        <section wire:key="group-{{ $dest ? $dest->id : $tenant->id }}" data-test="tenant-group">
            <div class="flex items-baseline justify-between border-b-2 border-zinc-900 pb-1 dark:border-zinc-100">
                <div>
                    <h2 class="text-sm font-extrabold uppercase tracking-wide">{{ $dest ? $dest->name : $tenant->display_name }}</h2>
                    <p class="text-xs text-zinc-500">{{ $tenant->display_name }}</p>
                </div>
                @if ($isOpen)
                    <span class="text-xs font-semibold text-red-600">Antrean {{ $wait->label($dest ?? $tenant) }}</span>
                @else
                    <span class="text-xs font-semibold text-zinc-500">Tutup · Buka pukul {{ $dest ? $dest->opensAtToday() : '08.00' }}</span>
                @endif
            </div>

            <ul class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @foreach ($tickets as $ticket)
                    @php($sellable = $isOpen && $ticket->is_available && $ticket->stock_qty > 0)
                    <li wire:key="ticket-{{ $ticket->id }}" @class(['flex items-center gap-3 py-3', 'opacity-50' => ! $sellable]) data-sellable="{{ $sellable ? '1' : '0' }}">
                        @if ($ticket->photoUrl())
                            <img src="{{ $ticket->photoUrl() }}" alt="" class="size-14 shrink-0 rounded object-cover">
                        @else
                            <span class="flex size-14 shrink-0 items-center justify-center rounded bg-zinc-200 text-sm font-bold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                {{ Str::upper(Str::substr($ticket->name, 0, 2)) }}
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $ticket->name }}</p>
                            <p class="truncate text-xs text-zinc-500">
                                {{ $ticket->description ? $ticket->description.' · ' : '' }}± {{ $ticket->prep_minutes }} mnt
                            </p>
                            <p class="text-sm font-semibold">Rp {{ number_format($ticket->price, 0, ',', '.') }}</p>
                        </div>

                        @unless ($ticket->is_available && $ticket->stock_qty > 0)
                            <span class="shrink-0 rounded border border-zinc-300 px-2 py-0.5 text-[10px] font-bold text-zinc-600">HABIS</span>
                        @endunless
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <x-empty-state title="Tiket tidak ditemukan" description="Coba kata kunci atau kategori lain." />
    @endforelse
</div>