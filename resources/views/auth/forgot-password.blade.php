<x-guest-layout>
    <p class="teks-bantu">
        Lupa password? Masukkan email akun Anda, kami akan mengirim tautan untuk membuat password baru.
    </p>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <form class="login-form" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-input" placeholder="nama@sekolah.sch.id"
                   value="{{ old('email') }}" required autofocus>
            @error('email')<p class="form-galat">{{ $message }}</p>@enderror
        </div>

        <button class="btn-submit" type="submit"><span>Kirim tautan reset password</span></button>
        <a class="tautan-bantu" href="{{ route('login') }}">Kembali ke halaman masuk</a>
    </form>
</x-guest-layout>
