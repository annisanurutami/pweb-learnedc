<x-guest-layout>
    <p class="teks-bantu">
        Ini area yang dilindungi. Masukkan password Anda untuk melanjutkan.
    </p>

    <form class="login-form" method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" class="form-input" required
                   autocomplete="current-password" autofocus>
            @error('password')<p class="form-galat">{{ $message }}</p>@enderror
        </div>

        <button class="btn-submit" type="submit"><span>Konfirmasi</span></button>
    </form>
</x-guest-layout>
