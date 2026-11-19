@if (session('success'))
    <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
@endif