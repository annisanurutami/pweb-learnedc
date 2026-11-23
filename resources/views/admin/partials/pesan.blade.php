@if (session('success'))
    <div class="notice" role="status">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="notice error" role="alert">{{ session('error') }}</div>
@endif
