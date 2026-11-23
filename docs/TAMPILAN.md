# Tampilan Learn's EDC (desain dari tim frontend)

Desain frontend (folder `pweb-learnedc (2)`) sudah disambungkan ke aplikasi ini.
Seluruh halaman yang memakai `<x-app-layout>` otomatis memakai kerangka baru
(navbar, sidebar per role, banner judul, footer), tanpa mengubah satu pun route,
nama field form, atau controller modul.

## Berkas

| Berkas | Fungsi |
|---|---|
| `resources/views/layouts/app.blade.php` | Kerangka halaman setelah login (navbar, sidebar, banner, dialog keluar) |
| `resources/views/layouts/guest.blade.php` | Kerangka halaman sebelum login (kartu login) |
| `resources/views/auth/*.blade.php` | Login, lupa password, reset, konfirmasi, verifikasi email |
| `resources/views/dashboard.blade.php` | Beranda semua role (sambutan dan kartu statistik) |
| `resources/views/admin/partials/pesan.blade.php` | Pesan sukses dan galat (`.notice`) |
| `app/Support/Menu.php` | Daftar menu sidebar per role |
| `app/Http/Controllers/DashboardController.php` | Menyiapkan statistik beranda dari data sungguhan |
| `public/css/learnedc/admin.css`, `app.css`, `login.css` | CSS asli dari desain, tidak diubah |
| `public/css/learnedc/bridge.css` | Menyelaraskan isi halaman berbasis Tailwind dengan desain |
| `public/js/learnedc/layout.js` | Sidebar mobile, menu profil, dialog keluar, jam WIB, pencarian tabel |
| `public/vendor/fontawesome/` | Ikon Font Awesome 6.7.2 (lokal, tidak perlu internet) |
| `lang/id.json`, `lang/id/*.php` | Terjemahan Bahasa Indonesia untuk Breeze dan pesan validasi |
| `tests/Feature/TampilanLearnedcTest.php` | Test tampilan |

## Menambah menu saat modul baru selesai

Buka `app/Support/Menu.php`, tambahkan satu baris pada konstanta `ITEM` di role yang sesuai:

```php
['Label menu', 'fa-nama-ikon', 'nama.route', ['pola.route.*']],
```

- Elemen ke-3 adalah nama route. Item otomatis dilewati selama route itu belum ada.
- Elemen ke-4 adalah pola route yang membuat menu tampil aktif (`request()->routeIs`).
- Nama ikon boleh dari Font Awesome Free 6 (`fa-solid`).

## Halaman baru

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Judul halaman</h2>
    </x-slot>

    ...isi halaman...
</x-app-layout>
```

Slot `header` tampil sebagai judul putih pada banner ungu. Tautan atau tombol aksi
di dalam slot (misalnya "Tambah") otomatis menjadi tombol putih. Subjudul banner diisi
role akun yang login.

Isi halaman boleh memakai kelas Tailwind (tabel, form, kartu, tombol) maupun kelas desain
(`.panel`, `.stat-grid`, `.notice`, dan lainnya di `app.css`). `bridge.css` menyamakan
warna dan bentuknya.

## Build Tailwind

Setelah menarik perubahan atau menambah kelas Tailwind baru, jalankan:

```
npm run build
```

atau `npm run dev` selama mengembangkan. Tanpa itu, kelas yang belum ada di build akan
tampil tanpa gaya (misalnya `py-3`, `text-red-700`).

## Yang belum diambil dari desain

Halaman prototipe berikut memang tidak ada di spesifikasi backend, jadi tidak dipasang
sebagai halaman: pengumuman, prestasi, pelanggaran (BK), tingkat kelas, setup admin, dan
role "guru piket". Peta role di desain (admin, guru mapel, guru piket, wali kelas, siswa)
juga berbeda dari role aplikasi (super admin, kepala sekolah, guru, wali kelas, bendahara,
siswa), sehingga menu mengikuti role aplikasi.

## Catatan

- Login memakai **email** (bawaan Breeze), bukan NIP atau NISN seperti teks contoh di desain.
- Lonceng notifikasi di navbar saat ini mengarah ke beranda. Sambungkan ke tabel
  `notifikasi` saat fitur pengingat wali kelas dibuat.
- Menu "Profil" bawaan Breeze masih menyediakan tombol **Hapus Akun**, padahal keputusan
  desain kita: akun tidak dihapus, hanya dinonaktifkan. Pertimbangkan menghapus bagian
  itu di `resources/views/profile/edit.blade.php`.
