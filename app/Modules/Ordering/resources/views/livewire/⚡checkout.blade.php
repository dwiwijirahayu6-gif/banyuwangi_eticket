<?php

use App\Models\CustomerSession;
use App\Modules\Ordering\Data\CheckoutDetails;
use App\Modules\Ordering\Exceptions\CheckoutException;
use App\Modules\Ordering\Services\CartService;
use App\Modules\Ordering\Services\CheckoutService;
use App\Modules\Ordering\Services\ResolveCustomerSession;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $destinationSlug = '';
    public string $idempotencyKey = '';
    
    // Data Pelanggan
    public string $customerName = '';
    public string $customerEmail = '';
    public string $customerPhone = '';

    public function mount(string $destinationSlug): void
    {
        $this->destinationSlug = $destinationSlug;
        $this->idempotencyKey = (string) Str::uuid(); // Kunci unik untuk mencegah double order
    }

    private function session(): ?CustomerSession
    {
        return app(ResolveCustomerSession::class)->current(request());
    }

    #[Computed]
    public function cart()
    {
        $session = $this->session();
        return $session ? app(CartService::class)->view($session) : null;
    }

    public function confirm(): void
    {
        $this->validate([
            'customerName' => ['required', 'string', 'max:100'],
            'customerEmail' => ['required', 'email', 'max:100'],
            'customerPhone' => ['required', 'string', 'max:20'],
        ], [], [
            'customerName' => 'Nama Lengkap',
            'customerEmail' => 'Email',
            'customerPhone' => 'Nomor WhatsApp',
        ]);

        $session = $this->session();
        if ($session === null) {
            $this->redirect(route('customer.home', ['destination' => $this->destinationSlug]));
            return;
        }

        $customerDetails = [
            'name' => $this->customerName,
            'email' => $this->customerEmail,
            'phone' => $this->customerPhone,
        ];

        try {
            // Panggil CheckoutService yang sudah kita buat
            $result = app(CheckoutService::class)->checkout($session, $this->idempotencyKey, null, $customerDetails);
            
            // Pasang cookie untuk status pesanan (jika diperlukan)
            if ($result->trackingToken !== null) {
                Cookie::queue(cookie(
                    name: 'order_tracking',
                    value: $result->trackingToken,
                    minutes: 240,
                    httpOnly: true,
                ));
            }

            // Lempar ke halaman status pesanan
            $this->redirectRoute('customer.order.show', ['destination' => $this->destinationSlug], navigate: false);

        } catch (CheckoutException $e) {
            // Jika error (misal kuota habis), kembalikan ke keranjang
            session()->flash('checkout_error', $e->getMessage());
            $this->redirect(route('customer.home', ['destination' => $this->destinationSlug]), navigate: false);
        }
    }
};
?>

@php($rupiah = fn (int $n) => 'Rp ' . number_format($n, 0, ',', '.'))

<div class="mx-auto max-w-2xl space-y-6 p-4">
    <h1 class="text-2xl font-extrabold">Checkout Pemesanan Tiket</h1>

    @if ($this->cart && ! $this->cart->isEmpty())
        <!-- Ringkasan Pesanan -->
        <div class="rounded-xl border border-zinc-200 bg-white p-4">
            <h2 class="mb-2 font-semibold">Ringkasan Tiket</h2>
            <ul class="divide-y divide-zinc-200 text-sm">
                @foreach ($this->cart->lines as $line)
                    <li class="flex justify-between py-2">
                        <div>
                            <span class="font-bold">{{ $line->quantity }}x</span> 
                            {{ $line->ticketTypeName }} 
                            <span class="text-xs text-zinc-500">({{ $line->visitDate }})</span>
                        </div>
                        <span class="font-semibold">{{ $rupiah($line->lineTotal) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3 flex justify-between border-t border-zinc-900 pt-2 text-lg font-bold">
                <span>Total Tagihan</span>
                <span>{{ $rupiah($this->cart->grandTotal) }}</span>
            </div>
        </div>

        <!-- Form Identitas -->
        <form wire:submit="confirm" class="space-y-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-4">
                <h2 class="mb-4 font-semibold">Data Pemesan</h2>
                
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium">Nama Lengkap</label>
                        <input type="text" wire:model="customerName" class="mt-1 w-full rounded-lg border-zinc-300 px-3 py-2 text-sm" placeholder="Sesuai KTP">
                        @error('customerName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Email</label>
                        <input type="email" wire:model="customerEmail" class="mt-1 w-full rounded-lg border-zinc-300 px-3 py-2 text-sm" placeholder="untuk e-tiket">
                        @error('customerEmail') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Nomor WhatsApp</label>
                        <input type="tel" wire:model="customerPhone" class="mt-1 w-full rounded-lg border-zinc-300 px-3 py-2 text-sm" placeholder="0812...">
                        @error('customerPhone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-red-600 py-3 text-center font-bold text-white hover:bg-red-700">
                Konfirmasi & Lanjut ke Pembayaran
            </button>
        </form>
    @else
        <div class="text-center">
            <p>Keranjang Anda kosong.</p>
            <a href="{{ route('customer.home', ['destination' => $this->destinationSlug]) }}" class="mt-4 inline-block text-blue-600 underline">Kembali ke Destinasi</a>
        </div>
    @endif
</div>