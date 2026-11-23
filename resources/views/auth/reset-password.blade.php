<x-guest-layout>
    <form class="login-form" method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-input"
                   value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @error('email')<p class="form-galat">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password baru</label>
            <input id="password" name="password" type="password" class="form-input" required autocomplete="new-password">
            @error('password')<p class="form-galat">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Ulangi password baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-input"
                   required autocomplete="new-password">
            @error('password_confirmation')<p class="form-galat">{{ $message }}</p>@enderror
        </div>

        <button class="btn-submit" type="submit"><span>Simpan password baru</span></button>
    </form>
</x-guest-layout>
