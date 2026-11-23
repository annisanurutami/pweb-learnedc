<x-guest-layout>
    <p class="teks-bantu">
        Sebelum melanjutkan, verifikasi alamat email Anda lewat tautan yang kami kirim.
        Jika belum menerimanya, kami bisa mengirim ulang.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success" role="status">
            Tautan verifikasi baru sudah dikirim ke email Anda.
        </div>
    @endif

    <div class="baris-tombol">
        <form class="login-form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn-submit" type="submit"><span>Kirim ulang email verifikasi</span></button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-tautan">Keluar</button>
        </form>
    </div>
</x-guest-layout>
