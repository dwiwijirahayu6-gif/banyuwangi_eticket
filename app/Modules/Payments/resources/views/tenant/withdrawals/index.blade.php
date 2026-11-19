<x-layouts.tenant :title="'Saldo & Penarikan Dana — ' . $tenant->display_name" :tenant="$tenant">
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Saldo &amp; Pengajuan Penarikan Dana</h2>
            <p class="text-sm text-slate-500">Pantau catatan buku besar (ledger), saldo pendapatan tiket, rekening bank terdaftar, dan ajukan pencairan dana.</p>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- 3 Kartu Status Keuangan --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Saldo Akumulasi Ledger --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block">Saldo Buku Besar (Ledger)</span>
                <p class="text-2xl font-extrabold text-slate-900 mt-2">Rp {{ number_format($balanceInfo['current_balance'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">Total mutasi bersih tersimpan</p>
            </div>

            {{-- Saldo Terkunci (Pending Withdrawal) --}}
            <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-xs">
                <span class="text-amber-800 text-xs font-semibold uppercase tracking-wider block">Saldo Terkunci (Pending)</span>
                <p class="text-2xl font-extrabold text-amber-700 mt-2">Rp {{ number_format($balanceInfo['pending_withdrawals'], 0, ',', '.') }}</p>
                <p class="text-xs text-amber-600 mt-1">Dalam proses verifikasi pencairan</p>
            </div>

            {{-- Saldo Tersedia Ditarik --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-xs">
                <span class="text-emerald-800 text-xs font-semibold uppercase tracking-wider block">Saldo Siap Ditarik</span>
                <p class="text-2xl font-extrabold text-emerald-700 mt-2">Rp {{ number_format($balanceInfo['available_balance'], 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-600 font-medium mt-1">Dapat diajukan untuk pencairan</p>
            </div>
        </div>

        {{-- Form Pengajuan Penarikan Dana & Rekening Terdaftar --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Form Ajukan Pencairan --}}
            <div class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Ajukan Penarikan Dana (Withdrawal)</span>
                </h3>

                @if ($bankAccounts->isEmpty())
                    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
                        <p class="font-bold">Belum Ada Rekening Bank Terdaftar!</p>
                        <p class="mt-1">Destinasi wisata Anda belum memiliki rekening bank terdaftar atau belum diverifikasi oleh Admin Pusat. Hubungi Administrator platform untuk menambahkan data rekening bank resmi Anda.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('tenant.saldo-penarikan.store', ['tenant' => $tenant->code]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="bank_account_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Rekening Bank Tujuan <span class="text-red-500">*</span></label>
                            <select id="bank_account_id" name="bank_account_id" required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                                <option value="">-- Pilih Rekening --</option>
                                @foreach ($bankAccounts as $acc)
                                    <option value="{{ $acc->id }}" @selected(old('bank_account_id') == $acc->id || $acc->is_primary)>
                                        {{ strtoupper($acc->bank_code) }} - ****{{ $acc->account_last4 }} a.n. {{ $acc->account_holder }} {{ $acc->is_primary ? '(Utama)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_account_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="amount" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nominal Penarikan (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 font-bold text-sm">Rp</span>
                                <input type="number" id="amount" name="amount" required min="10000" max="{{ (int) $balanceInfo['available_balance'] }}" step="1000"
                                       value="{{ old('amount') }}" placeholder="Minimal Rp 10.000"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-11 pr-3.5 py-2.5 text-sm font-bold text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                            </div>
                            <div class="flex justify-between text-[11px] text-slate-400 mt-1">
                                <span>Minimal penarikan: Rp 10.000</span>
                                <span>Maksimal: Rp {{ number_format($balanceInfo['available_balance'], 0, ',', '.') }}</span>
                            </div>
                            @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-xl bg-blue-50/60 border border-blue-100 p-3 text-[11px] text-blue-900 leading-relaxed">
                            <strong>Ketentuan Pengajuan:</strong>
                            <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-800">
                                <li>Saldo akan langsung dikunci <em>(locked)</em> begitu pengajuan berstatus <code>pending</code>.</li>
                                <li>Persetujuan dan transfer pencairan dana diproses oleh Admin Pusat / Dinas Pariwisata.</li>
                                <li>Admin Tenant tidak dapat menyetujui pengajuannya sendiri sesuai aturan kepatuhan audit.</li>
                            </ul>
                        </div>

                        <button type="submit" @disabled($balanceInfo['available_balance'] < 10000)
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 disabled:opacity-50 hover:bg-blue-700 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Kirim Pengajuan Pencairan Dana</span>
                        </button>
                    </form>
                @endif
            </div>

            {{-- Rekening Bank Terdaftar --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Rekening Bank Terdaftar</h3>

                @if ($bankAccounts->isEmpty())
                    <p class="text-xs text-slate-400 py-4">Belum ada rekening yang terhubung.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($bankAccounts as $acc)
                            <div class="rounded-xl border border-slate-200 p-3.5 bg-slate-50/50">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs text-slate-900">{{ strtoupper($acc->bank_code) }}</span>
                                    <span class="inline-flex rounded-full px-2 py-0.2 text-[10px] font-bold 
                                        {{ $acc->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $acc->status === 'verified' ? 'Terverifikasi' : ucfirst($acc->status) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 font-mono">****{{ $acc->account_last4 }}</p>
                                <p class="text-xs text-slate-800 font-medium mt-0.5">a.n. {{ $acc->account_holder }}</p>
                                @if ($acc->is_primary)
                                    <span class="inline-block mt-2 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">Rekening Utama</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Riwayat Pengajuan Withdrawal --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Riwayat Pengajuan Penarikan Dana</h3>
                <span class="text-xs text-slate-500">Transisi: Pending → Approved → Disbursed</span>
            </div>

            @if ($withdrawals->isEmpty())
                <div class="p-8">
                    <x-empty-state title="Belum Ada Pengajuan Penarikan" message="Riwayat pengajuan pencairan saldo akan tercatat di sini." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-3 px-4">Waktu Pengajuan</th>
                                <th class="py-3 px-4 text-right">Nominal Pencairan</th>
                                <th class="py-3 px-4">Rekening Tujuan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4">Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($withdrawals as $w)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 text-xs font-medium text-slate-900">
                                        {{ $w->created_at ? $w->created_at->format('d/m/Y H:i') : '-' }} WIB
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">
                                        Rp {{ number_format($w->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-600 font-mono">
                                        {{ $w->bank_account_info }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold 
                                            {{ $w->status === 'disbursed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                              ($w->status === 'approved' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                                              ($w->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200')) }}">
                                            {{ ucfirst($w->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-500">
                                        {{ $w->approvedBy?->name ?? ($w->status === 'pending' ? 'Menunggu Admin Pusat' : '-') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        </div>

        {{-- Buku Besar Mutasi (Ledger Entries) --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Buku Besar Transaksi Keuangan (Ledger)</h3>
                <span class="text-xs text-slate-500">Catatan append-only yang tidak dapat dihapus</span>
            </div>

            @if ($ledgerEntries->isEmpty())
                <div class="p-8">
                    <x-empty-state title="Belum Ada Catatan Ledger" message="Catatan mutasi kredit penjualan tiket dan debit pencairan akan otomatis tercatat di sini." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-3 px-4">Tanggal &amp; Waktu</th>
                                <th class="py-3 px-4">Tipe Mutasi</th>
                                <th class="py-3 px-4">Keterangan Transaksi</th>
                                <th class="py-3 px-4 text-right">Nominal</th>
                                <th class="py-3 px-4 text-right">Saldo Setelah Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach ($ledgerEntries as $entry)
                                @php
                                    $isCredit = in_array($entry->transaction_type, ['credit_sale', 'refund']);
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 text-xs text-slate-500">
                                        {{ $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : '-' }} WIB
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold 
                                            {{ $isCredit ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                            {{ $entry->transaction_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-700">
                                        @if ($entry->bookingItem)
                                            Penjualan: {{ $entry->bookingItem->ticketType->name ?? 'Tiket' }} ({{ $entry->bookingItem->quantity }} tiket)
                                        @elseif ($entry->withdrawal)
                                            Pencairan Dana #{{ $entry->withdrawal_id }}
                                        @else
                                            Mutasi Sistem #{{ $entry->id }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold {{ $isCredit ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $isCredit ? '+' : '-' }} Rp {{ number_format($entry->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-extrabold text-slate-900">
                                        Rp {{ number_format($entry->balance_after, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100">
                    {{ $ledgerEntries->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.tenant>
