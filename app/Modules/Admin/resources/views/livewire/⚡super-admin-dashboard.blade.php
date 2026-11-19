<?php

use App\Models\Booking;
use App\Models\Tenant;
use App\Models\Destination;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function stats()
    {
        return [
            // Mengambil total pendapatan dari booking yang sudah lunas
            'total_revenue' => Booking::where('status', 'paid')->sum('total_amount'),
            
            // Mengambil total tiket terjual (menghitung quantity di booking_items)
            'total_tickets' => \App\Models\BookingItem::whereHas('booking', function($q) {
                $q->where('status', 'paid');
            })->sum('quantity'),
            
            'total_tenants' => Tenant::count(),
            'total_destinations' => Destination::count(),
        ];
    }

    #[Computed]
    public function recentBookings()
    {
        return Booking::latest()->take(5)->get();
    }

    #[Computed]
    public function recentTenants()
    {
        return Tenant::latest()->take(5)->get();
    }
};
?>

<div class="space-y-6">
    <!-- Kartu Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="text-sm text-zinc-500 mb-1">Total Pendapatan</div>
            <div class="text-2xl font-bold text-green-600">Rp {{ number_format($this->stats['total_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="text-sm text-zinc-500 mb-1">Tiket Terjual</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($this->stats['total_tickets'], 0, ',', '.') }} <span class="text-sm font-normal text-zinc-400">Tiket</span></div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="text-sm text-zinc-500 mb-1">Pengelola Wisata</div>
            <div class="text-2xl font-bold text-purple-600">{{ $this->stats['total_tenants'] }} <span class="text-sm font-normal text-zinc-400">Tenant</span></div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="text-sm text-zinc-500 mb-1">Total Destinasi</div>
            <div class="text-2xl font-bold text-orange-600">{{ $this->stats['total_destinations'] }} <span class="text-sm font-normal text-zinc-400">Tempat</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Pesanan Terbaru -->
        <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-zinc-200 bg-zinc-50 flex justify-between items-center">
                <h3 class="font-bold text-zinc-800">Pesanan Terbaru</h3>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($this->recentBookings as $booking)
                    <div class="p-4 flex justify-between items-center hover:bg-zinc-50 transition">
                        <div>
                            <div class="font-medium text-sm">{{ $booking->customer_name }}</div>
                            <div class="text-xs text-zinc-500">{{ $booking->booking_code }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-sm">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</div>
                            <x-status-badge :status="$booking->status === 'paid' ? 'active' : 'pending'">
                                {{ strtoupper($booking->status) }}
                            </x-status-badge>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-zinc-500">Belum ada pesanan.</div>
                @endforelse
            </div>
        </div>

        <!-- Pengelola Terbaru -->
        <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-zinc-200 bg-zinc-50 flex justify-between items-center">
                <h3 class="font-bold text-zinc-800">Pengelola Baru</h3>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.tenants.index') }}" class="text-xs text-zinc-500 hover:text-zinc-800">Lihat Semua</a>
                    <a href="{{ route('admin.tenants.create') }}" class="text-xs text-emerald-600 font-semibold hover:underline">+ Tambah Wisata</a>
                </div>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($this->recentTenants as $tenant)
                    <div class="p-4 flex justify-between items-center hover:bg-zinc-50 transition">
                        <div>
                            <div class="font-medium text-sm">{{ $tenant->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $tenant->code }}</div>
                        </div>
                        <x-status-badge :status="$tenant->status === 'active' ? 'active' : 'default'">
                            {{ strtoupper($tenant->status) }}
                        </x-status-badge>
                    </div>
                @empty
                    <div class="p-4 text-center text-zinc-500">Belum ada tenant.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>