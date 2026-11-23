<x-guest-layout>
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="login-form" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-input" placeholder="nama@sekolah.sch.id"
                   value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" class="form-input" placeholder="Masukkan password..."
                   required autocomplete="current-password">
        </div>

        <label for="remember_me" class="form-ingat">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Ingat saya di perangkat ini</span>
        </label>

        <button class="btn-submit" type="submit"><span class="btn-icon">&#x1F510;</span><span>Masuk ke Learn's EDC</span></button>

        @if (Route::has('password.request'))
            <a class="tautan-bantu" href="{{ route('password.request') }}">Lupa password?</a>
        @endif
    </form>
</x-guest-layout>
