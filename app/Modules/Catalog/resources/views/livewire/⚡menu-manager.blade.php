<?php

use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Models\UserTenantRole;
use App\Modules\Admin\Services\AuditLogger;
use App\Modules\Catalog\Services\TicketPhotoStore;
use App\Modules\Catalog\Services\TicketStockService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Manajer tiket tenant (UC-13 & UC-14).
 * tenantId adalah prop publik (tidak dipercaya): booted() memverifikasi ulang
 * keanggotaan aktor DAN mengisi TenantContext pada SETIAP request (Livewire update
 * tidak melewati middleware route). Semua query ter-scope otomatis ke tenant aktif.
 * UC-13: tambah/ubah tiket (deskripsi, waktu layanan, foto WebP <= 2 MB), hapus = soft delete, cari + saring.
 * UC-14: sakelar ketersediaan satu ketukan.
 */
new class extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;
    public int $tenantId = 0;
    public string $name = '';
    public ?int $destinationId = null;
    public string $category = 'Tiket Masuk';
    public string $description = '';
    public int $basePrice = 25000;
    public int $prepMinutes = 5;
    public int $stockQty = 100;
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $photo = null;

    public string $search = '';
    public ?string $filterCategory = null;

    public function mount(int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $firstDest = Destination::query()->withoutGlobalScope('tenant')->where('tenant_id', $this->tenantId)->first();
        if ($firstDest) {
            $this->destinationId = $firstDest->id;
        }
    }

    public function booted(): void
    {
        $user = Auth::user();
        $isMember = $user !== null && UserTenantRole::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $this->tenantId)
            ->exists();

        abort_unless($isMember, 403);

        app(TenantContext::class)->set(Tenant::query()->findOrFail($this->tenantId));
    }

    #[Computed]
    public function destinations(): \Illuminate\Database\Eloquent\Collection
    {
        return Destination::query()->withoutGlobalScope('tenant')->where('tenant_id', $this->tenantId)->orderBy('name')->get();
    }

    #[Computed]
    public function categories(): array
    {
        return ['Tiket Masuk', 'Tiket Terusan', 'Tiket Anak', 'Tiket Mancanegara', 'Wahana & Atraksi', 'Parkir & Fasilitas'];
    }

    #[Computed]
    public function tickets(): \Illuminate\Database\Eloquent\Collection
    {
        return TicketType::query()
            ->with(['destination:id,name'])
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->filterCategory !== null && $this->filterCategory !== '', fn ($query) => $query->where('category', $this->filterCategory))
            ->orderBy('name')
            ->get();
    }

    /** @return array<string, list<string>> */
    private function ticketRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'destinationId' => ['required', 'integer'],
            'category' => ['required', 'string', 'max:60'],
            'basePrice' => ['required', 'integer', 'min:1'],
            'prepMinutes' => ['required', 'integer', 'min:1', 'max:240'],
            'stockQty' => ['required', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama tiket',
            'description' => 'deskripsi',
            'destinationId' => 'destinasi',
            'category' => 'kategori',
            'basePrice' => 'harga',
            'prepMinutes' => 'waktu siap',
            'stockQty' => 'stok',
            'photo' => 'foto',
        ];
    }

    public function createTicket(): void
    {
        $data = $this->validate($this->ticketRules());

        $destination = Destination::query()->withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantId)
            ->findOrFail($data['destinationId']);

        $ticket = new TicketType();
        $ticket->forceFill([
            'tenant_id' => $this->tenantId,
            'destination_id' => $destination->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'description' => $data['description'] ?: null,
            'price' => $data['basePrice'],
            'prep_minutes' => $data['prepMinutes'],
            'stock_qty' => $data['stockQty'],
            'is_available' => true,
            'photo_path' => $this->photo ? app(TicketPhotoStore::class)->store($this->photo, $this->tenantId) : null,
        ]);
        $ticket->save();

        $this->resetForm();
        session()->flash('status', 'Tiket berhasil ditambahkan.');
    }

    public function createMenu(): void
    {
        $this->createTicket();
    }

    public function edit(int $ticketId): void
    {
        $ticket = TicketType::query()->findOrFail($ticketId);
        $this->editingId = $ticket->id;
        $this->name = $ticket->name;
        $this->description = (string) $ticket->description;
        $this->destinationId = $ticket->destination_id;
        $this->category = $ticket->category ?? 'Tiket Masuk';
        $this->basePrice = (int) $ticket->price;
        $this->prepMinutes = (int) $ticket->prep_minutes;
        $this->stockQty = (int) $ticket->stock_qty;
        $this->photo = null;
    }

    public function updateTicket(): void
    {
        $data = $this->validate($this->ticketRules());
        $ticket = TicketType::query()->findOrFail((int) $this->editingId);

        $destination = Destination::query()->withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantId)
            ->findOrFail($data['destinationId']);

        $before = $ticket->only(['name', 'price', 'prep_minutes', 'destination_id', 'category']);
        $photoStore = app(TicketPhotoStore::class);

        if ($this->photo) {
            $photoStore->delete($ticket->photo_path);
            $ticket->photo_path = $photoStore->store($this->photo, $this->tenantId);
        }

        $ticket->forceFill([
            'destination_id' => $destination->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'description' => $data['description'] ?: null,
            'price' => $data['basePrice'],
            'prep_minutes' => $data['prepMinutes'],
            'stock_qty' => $data['stockQty'],
        ])->save();

        app(AuditLogger::class)->record(
            'ticket_type',
            $ticket->id,
            'updated',
            $before,
            $ticket->only(['name', 'price', 'prep_minutes', 'destination_id', 'category']),
            $this->tenantId
        );

        $this->resetForm();
        session()->flash('status', 'Tiket berhasil diperbarui.');
    }

    public function updateMenu(): void
    {
        $this->updateTicket();
    }

    public function deleteTicket(int $ticketId): void
    {
        $ticket = TicketType::query()->findOrFail($ticketId);
        $ticket->delete();

        app(AuditLogger::class)->record('ticket_type', $ticket->id, 'deleted', ['name' => $ticket->name], null, $this->tenantId);

        unset($this->tickets);
        session()->flash('status', "Tiket {$ticket->name} berhasil dihapus.");
    }

    public function deleteMenu(int $ticketId): void
    {
        $this->deleteTicket($ticketId);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'basePrice', 'prepMinutes', 'stockQty', 'photo', 'editingId']);
        $this->resetValidation();
        unset($this->tickets);
    }

    public function toggle(int $ticketId): void
    {
        $ticket = TicketType::query()->findOrFail($ticketId);
        app(TicketStockService::class)->toggleAvailability($ticket);
        unset($this->tickets);
    }

    public function restock(int $ticketId, int $delta = 10): void
    {
        $ticket = TicketType::query()->findOrFail($ticketId);
        app(TicketStockService::class)->adjust($ticket, $delta, 'restock', (string) Str::uuid(), 'manual restock kuota');
        unset($this->tickets);
    }
};
?>

<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
        <h2 class="font-semibold">{{ $editingId ? 'Ubah Tiket' : 'Tambah Jenis Tiket' }}</h2>
        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <x-input name="name" label="Nama Tiket" wire:model="name" />
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Destinasi Wisata</label>
                <select wire:model="destinationId" class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    <option value="">Pilih Destinasi</option>
                    @foreach ($this->destinations as $dest)
                        <option value="{{ $dest->id }}" wire:key="dest-{{ $dest->id }}">{{ $dest->name }}</option>
                    @endforeach
                </select>
                @error('destinationId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Kategori Tiket</label>
                <select wire:model="category" class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat }}" wire:key="cat-{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-input name="basePrice" label="Harga Tiket (Rp)" type="number" wire:model="basePrice" />
                @error('basePrice') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <x-input name="description" label="Deskripsi / Syarat Tiket (opsional)" wire:model="description" />
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-input name="prepMinutes" label="Waktu Pelayanan / Siap (menit)" type="number" wire:model="prepMinutes" />
                @error('prepMinutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-input name="stockQty" label="Kuota / Stok Harian" type="number" wire:model="stockQty" />
                @error('stockQty') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-1 sm:col-span-2">
                <label for="photo" class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Foto Tiket/Atraksi (JPG/PNG/WebP, maks 2 MB)</label>
                <input id="photo" type="file" wire:model="photo" accept="image/*" class="text-sm" />
                @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            @if ($editingId)
                <x-button type="button" wire:click="updateTicket">Simpan Perubahan</x-button>
                <x-button type="button" variant="secondary" wire:click="cancelEdit">Batal</x-button>
            @else
                <x-button type="button" wire:click="createTicket">Simpan Tiket</x-button>
            @endif
        </div>
    </section>

    <section>
        <div class="flex flex-wrap items-end justify-between gap-2">
            <h2 class="font-semibold text-lg">Daftar Tiket &amp; Ketersediaan</h2>
            <div class="flex flex-wrap gap-2">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama tiket…"
                    class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
                <select wire:model.live="filterCategory" class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    <option value="">Kategori: Semua</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat }}" wire:key="fcat-{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
            <table class="w-full min-w-[50rem] text-left text-sm">
                <thead class="bg-zinc-100 text-xs uppercase tracking-wide text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="px-4 py-2.5">Foto</th>
                        <th class="px-4 py-2.5">Nama Tiket</th>
                        <th class="px-4 py-2.5">Destinasi / Kategori</th>
                        <th class="px-4 py-2.5">Harga</th>
                        <th class="px-4 py-2.5">± Siap</th>
                        <th class="px-4 py-2.5">Stok</th>
                        <th class="px-4 py-2.5">Ketersediaan</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->tickets as $ticket)
                        <tr wire:key="ticket-{{ $ticket->id }}" class="border-t border-zinc-200 dark:border-zinc-800">
                            <td class="px-4 py-2.5">
                                @if ($ticket->photoUrl())
                                    <img src="{{ $ticket->photoUrl() }}" alt="" class="size-11 rounded object-cover">
                                @else
                                    <span class="flex size-11 items-center justify-center rounded bg-zinc-200 text-xs font-bold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                        {{ Str::upper(Str::substr($ticket->name, 0, 2)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-medium">
                                {{ $ticket->name }}
                                @unless ($ticket->is_available && $ticket->stock_qty > 0)
                                    <span class="ms-1 rounded bg-red-100 px-1.5 text-[10px] font-bold text-red-700">HABIS</span>
                                @endunless
                            </td>
                            <td class="px-4 py-2.5 text-zinc-600 dark:text-zinc-400">
                                {{ $ticket->destination?->name }} · {{ $ticket->category }}
                            </td>
                            <td class="px-4 py-2.5 font-semibold">
                                Rp {{ number_format($ticket->price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5">{{ $ticket->prep_minutes }} mnt</td>
                            <td class="px-4 py-2.5 {{ $ticket->stock_qty <= 10 ? 'font-semibold text-amber-600' : '' }}">
                                {{ $ticket->stock_qty }}
                            </td>
                            <td class="px-4 py-2.5">
                                <button type="button" role="switch" aria-checked="{{ $ticket->is_available ? 'true' : 'false' }}" wire:click="toggle({{ $ticket->id }})"
                                    class="inline-flex min-h-11 items-center gap-2 cursor-pointer" aria-label="Ketersediaan {{ $ticket->name }}">
                                    <span @class(['relative inline-block h-6 w-11 rounded-full transition', 'bg-green-600' => $ticket->is_available, 'bg-zinc-400' => ! $ticket->is_available])>
                                        <span @class(['absolute top-0.5 size-5 rounded-full bg-white transition', 'left-5' => $ticket->is_available, 'left-0.5' => ! $ticket->is_available])></span>
                                    </span>
                                    <span class="text-xs">{{ $ticket->is_available ? 'Tersedia' : 'Habis' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                <button type="button" wire:click="edit({{ $ticket->id }})" class="me-2 min-h-11 text-sm underline cursor-pointer">Edit</button>
                                <button type="button" wire:click="restock({{ $ticket->id }}, 10)" class="me-2 min-h-11 text-sm underline cursor-pointer">+10 stok</button>
                                <button type="button" wire:click="deleteTicket({{ $ticket->id }})" wire:confirm="Hapus tiket {{ $ticket->name }}? Riwayat transaksi tetap utuh." class="min-h-11 text-sm text-red-700 underline dark:text-red-400 cursor-pointer">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3" colspan="8">
                                <x-empty-state title="Belum ada tiket" description="Silakan buat tiket baru untuk destinasi Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-zinc-500">
            Sakelar ketersediaan = satu ketukan (UC-14); katalog pelanggan memperbarui status dalam ≤ 5 detik. Hapus = soft delete.
        </p>
    </section>
</div>