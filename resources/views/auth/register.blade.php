
<x-layouts.customer :title="'Daftar Akun'">

    <div class="mx-auto max-w-md">

        <h1 class="mb-2 text-2xl font-bold">
            Daftar Akun
        </h1>

        <p class="mb-6 text-gray-600">
            Buat akun untuk menikmati pemesanan tiket
            wisata Banyuwangi.
        </p>

        <form method="POST" action="{{ route('register.store') }}"
              class="flex flex-col gap-4">

            @csrf

            <div>
                <label for="name">Nama Lengkap</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full rounded border p-2"
                >
                @error('name')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded border p-2"
                >
                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    class="w-full rounded border p-2"
                >
                @error('password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">
                    Konfirmasi Password
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    class="w-full rounded border p-2"
                >
            </div>

            <button
                type="submit"
                class="rounded bg-blue-600 px-4 py-2 text-white">
                Daftar Sekarang
            </button>

        </form>

        <p class="mt-4 text-sm">
            Sudah memiliki akun?
            <a href="{{ route('login') }}"
               class="text-blue-600 underline">
                Login di sini
            </a>
        </p>

    </div>

</x-layouts.customer>
