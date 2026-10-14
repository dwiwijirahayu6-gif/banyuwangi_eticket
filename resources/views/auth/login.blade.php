<x-layouts.customer :title="'Login'">
    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf
        <x-input name="email" label="Email" type="email" />
        <x-input name="password" label="Password" type="password" />
        <x-button type="submit">Login</x-button>
    </form>

    @if ($errors->any())
        <p class="mt-4 text-sm text-red-600">{{ $errors->first() }}</p>
    @endif
</x-layouts.customer>