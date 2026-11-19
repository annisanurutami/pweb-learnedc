# LearnEdc – Aplikasi E-Learning Sekolah

Aplikasi E-Learning sekolah berbasis web dengan 6 role: Super Admin, Guru, Wali Kelas,
Bendahara, Kepala Sekolah, dan Siswa. Dibuat untuk tugas kelompok Pemrograman Web.

- **Backend:** Laravel 13 (PHP 8.3+), MySQL 8
- **Frontend:** Blade, Tailwind CSS, Alpine.js (bawaan Laravel Breeze)
- **Autentikasi:** Laravel Breeze, hak akses per role dengan `spatie/laravel-permission`
- **Paket lain:** `barryvdh/laravel-dompdf` (cetak rapor PDF), `maatwebsite/excel` (ekspor/impor Excel), Chart.js (grafik, dipasang belakangan)
- **Bahasa antarmuka:** Bahasa Indonesia

---

## 1. Kebutuhan sistem

| Kebutuhan | Versi |
|---|---|
| PHP | 8.3 atau lebih baru (diuji di 8.4) |
| Composer | 2.x |
| Node.js dan npm | LTS terbaru |
| MySQL | 8.x (diuji di 8.4.3) |
| Git | terbaru |

Disarankan memakai **Laragon** di Windows (sudah berisi PHP, MySQL, dan Apache).

Ekstensi PHP yang harus aktif: `zip`, `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `gd`.
Ekstensi **`zip`** paling sering lupa diaktifkan, dan tanpanya `composer install` gagal.
Di Laragon: klik kanan ikon Laragon, pilih *PHP*, lalu *Extensions*, dan centang `zip`.

---

## 2. Cara instalasi

### 2.1 Ambil kode

```bash
git clone https://github.com/annisanurutami/pweb-learnedc.git
cd pweb-learnedc
git checkout abiyyu-backend
```

> Branch kerja backend adalah `abiyyu-backend`. Frontend bekerja di branch sendiri (lihat bagian 5).

### 2.2 Pasang dependensi

```bash
composer install
npm install
```

### 2.3 Siapkan file `.env`

```bash
copy .env.example .env
php artisan key:generate
```

Buka `.env`, lalu ubah bagian berikut:

```
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=learnedcpweb
DB_USERNAME=root
DB_PASSWORD=
```

> `.env` berisi rahasia aplikasi dan **tidak boleh di-commit** (sudah ada di `.gitignore`).
> Jangan mengirim screenshot isi `.env` ke siapa pun.

### 2.4 Buat database

Buka phpMyAdmin atau HeidiSQL (di Laragon: tombol *Database*), lalu jalankan:

```sql
CREATE DATABASE learnedcpweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2.5 Bangun tabel dan isi data contoh

```bash
php artisan migrate:fresh --seed
```

Perintah ini **menghapus semua tabel** di database `learnedcpweb`, membangunnya ulang, lalu mengisi
data contoh (role, hari, akun demo, kelas, mapel, jadwal). Aman dijalankan kapan saja
selama belum ada data sungguhan.

### 2.6 Jalankan aplikasi

Buka **dua terminal**:

```bash
# Terminal 1: server aplikasi
php artisan serve

# Terminal 2: tampilan (CSS/JS, otomatis refresh saat file disimpan)
npm run dev
```

Buka http://127.0.0.1:8000 di browser. Untuk memakai hasil build tanpa `npm run dev`,
jalankan `npm run build` satu kali.

---

## 3. Akun demo

**Password semua akun: `password`**

Akun ini dibuat otomatis oleh seeder dan hanya untuk pengembangan.

| Role | Nama | Email | Keterangan |
|---|---|---|---|
| Super Admin | Super Admin | `admin@sekolah.test` | Mengelola semua data master dan akun |
| Kepala Sekolah | Kepala Sekolah Demo | `kepsek@sekolah.test` | Hanya melihat (read-only) |
| Guru + Wali Kelas | Bu Ratna | `walikelas@sekolah.test` | Wali kelas X-A, mengajar Matematika di X-A dan X-B |
| Guru + Wali Kelas | Pak Hendra | `walikelas2@sekolah.test` | Wali kelas X-B |
| Guru | Pak Agus | `guru@sekolah.test` | Mengajar Bahasa Indonesia (X-A, X-B) dan IPA (X-A) |
| Siswa + Bendahara | Bendahara Demo | `bendahara@sekolah.test` | Kelas X-A, bendahara kas, piket hari Selasa |
| Siswa | Siswa Demo | `siswa@sekolah.test` | Kelas X-A, piket hari Senin |
| Siswa | Citra Lestari | `siswa3@sekolah.test` | Kelas X-A, ketua kelas |
| Siswa | Dewi Anggraini | `siswa4@sekolah.test` | Kelas X-A |
| Siswa | Eka Putra | `siswa5@sekolah.test` | Kelas X-A |
| Siswa | Fitri Handayani | `siswa6@sekolah.test` | Kelas X-B |
| Siswa | Gilang Ramadhan | `siswa7@sekolah.test` | Kelas X-B |
| Siswa | Hana Safitri | `siswa8@sekolah.test` | Kelas X-B |

Total 13 akun. NIS siswa: `2026001` sampai `2026008`.

### Data contoh lainnya

| Data | Isi |
|---|---|
| Tahun ajaran | 2026/2027 (aktif) |
| Semester | Ganjil (aktif) dan Genap |
| Bobot nilai | Tugas 40%, Ujian 60% |
| Kelas | X-A (wali: Bu Ratna), X-B (wali: Pak Hendra), iuran kas Rp 10.000 per bulan |
| Mata pelajaran | MTK, BIN, IPA |
| Hari aktif | Senin sampai Jumat |
| Jadwal | X-A: Senin MTK 07:30 dan BIN 09:15, Selasa IPA 07:30. X-B: Rabu MTK 07:30 dan BIN 09:15 |

---

## 4. Role dan hak akses

Hak akses memakai role dari `spatie/laravel-permission`. Satu akun bisa punya lebih dari satu role.

| Role | Cara menentukan |
|---|---|
| `super_admin`, `kepala_sekolah` | Role pada akun |
| `guru` | Role pada akun, dan punya baris di tabel `guru` |
| `wali_kelas` | Role pada akun, dan namanya ada di `kelas.wali_kelas_id` |
| `siswa` | Role pada akun, dan punya baris di tabel `siswa` |
| `bendahara` | Role tambahan pada akun siswa, dicatat juga di tabel `role_siswa` |

Aturan penting:
- Guru hanya boleh mengelola kelas dan mapel yang ditugaskan kepadanya (tabel `guru_mapel`).
- Siswa hanya boleh melihat data dirinya sendiri.
- Semua data akademik terikat Tahun Ajaran dan Semester yang aktif.
- Akun dibuat oleh Super Admin. Pendaftaran mandiri (`/register`) akan dinonaktifkan.

---

## 5. Pembagian kerja dan aturan Git

### Pemilik folder

| Pemilik | Folder |
|---|---|
| Backend | `app/`, `routes/`, `database/`, `config/` |
| Frontend | `resources/views/`, `resources/css/`, `resources/js/` |

Masing-masing hanya mengedit foldernya sendiri supaya jarang terjadi konflik Git.
Kalau butuh route atau data baru, minta ke backend. Kalau backend butuh perubahan tampilan, minta ke frontend.

### Struktur tampilan

Halaman diletakkan per role di `resources/views`:

```
resources/views/
  admin/          Super Admin
  guru/           Guru
  wali-kelas/     Wali Kelas
  siswa/          Siswa
  bendahara/      Bendahara
  kepala-sekolah/ Kepala Sekolah
  components/     Potongan tampilan yang dipakai berulang (tabel, kartu, badge, modal)
  layouts/        Kerangka halaman
  auth/           Login dan lupa password
```

Tampilan ditulis dalam **Blade** (HTML dengan perintah seperti `@foreach` dan `{{ }}`), diberi gaya dengan class **Tailwind CSS**.

### Format commit

```
<tipe>(<lingkup>): <deskripsi singkat, huruf kecil>
```

| Tipe | Dipakai untuk |
|---|---|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `chore` | Setup, paket, konfigurasi |
| `refactor` | Merapikan kode tanpa mengubah perilaku |
| `test` | Menambah test |
| `docs` | README dan dokumentasi |

Contoh: `feat(db): tambah migration tabel kelas`, `feat(guru): upload materi`, `fix(ui): perbaiki tabel nilai`.
Satu commit untuk satu hal, dan commit setiap langkah selesai dan sudah dites.

### Alur harian

```bash
git pull origin <nama-branch>      # ambil perubahan terbaru
# ...kerjakan...
git status                         # pastikan tidak ada .env, vendor, node_modules
git add <file yang terkait>
git commit -m "feat(...): ..."
git push origin <nama-branch>
```

Setelah ada perubahan database dari teman, jalankan `php artisan migrate:fresh --seed`
supaya tabel dan data contohmu sama dengan tim.

---

## 6. Struktur database

Database `learnedcpweb`, 31 tabel buatan sendiri ditambah tabel sistem Laravel dan Spatie.

| Kelompok | Tabel |
|---|---|
| Akun | `users` (kolom `is_active`), `guru`, `siswa` |
| Data master | `tahun_ajaran`, `semester`, `hari`, `mata_pelajaran`, `kelas`, `kelas_siswa`, `guru_mapel`, `jadwal_pelajaran` |
| Pembelajaran | `materi`, `tugas`, `pengumpulan_tugas`, `ujian`, `soal_ujian`, `pengerjaan_ujian`, `jawaban_ujian` |
| Absensi | `presensi_siswa`, `presensi_guru` |
| Nilai dan rapor | `nilai`, `bobot_nilai`, `audit_nilai`, `catatan_karakter`, `rapor` |
| Siswa dan piket | `role_siswa`, `jadwal_piket`, `dokumentasi_piket` |
| Kas | `kas_pembayaran`, `kas_pengeluaran` (kolom `kelas.iuran_kas`) |
| Sistem | `notifikasi`, `log_login` |

Model Eloquent ada di `app/Models` (satu model per tabel). Semua nama tabel memakai bentuk tunggal,
jadi setiap model menulis `protected $table` sendiri.

### Keputusan desain

- **Nilai:** tabel `nilai` adalah satu-satunya sumber untuk laporan (rapor, rata-rata, kelengkapan).
  Nilai mentah ada di `pengumpulan_tugas.nilai` dan `pengerjaan_ujian.nilai_akhir`, dan semuanya ditulis ke `nilai` lewat satu fungsi.
- **Nilai akhir** per mapel = (rata-rata tugas x bobot tugas + rata-rata ujian x bobot ujian) / 100. Bobot ada di `bobot_nilai`.
- **Absensi:** `presensi_siswa.jadwal_id` terisi untuk absensi per pelajaran (guru), dan kosong untuk absensi harian kelas (wali kelas).
- **Ujian:** `pengerjaan_ujian` unik per siswa dan ujian, sehingga siswa hanya bisa mengerjakan sekali. Sisa waktu dihitung di server dari `mulai_at` ditambah `durasi_menit`.
- **Kas:** saldo tidak disimpan, dihitung dari total pembayaran dikurangi total pengeluaran. Tunggakan = periode belum lunas dikali `kelas.iuran_kas`.
- **Rapor** tetap bisa dicetak saat nilai belum lengkap, dengan peringatan dan status `draft`.
- **Akun nonaktif** (`is_active = false`) tidak bisa login. Akun tidak dihapus karena data nilai, absensi, dan kas menunjuk padanya.
- **Notifikasi** memakai tabel sendiri (`notifikasi`), bukan sistem notifikasi bawaan Laravel.

---

## 7. Status pengerjaan

| Tahap | Status |
|---|---|
| Setup proyek, Breeze, Spatie | Selesai |
| Migration (31 tabel dan 2 kolom tambahan) | Selesai |
| Model Eloquent beserta relasinya (32 model) | Selesai |
| Seeder role, hari, dan data contoh | Dikerjakan |
| Middleware role dan redirect dashboard per role | Belum |
| Modul Super Admin (data master dan akun) | Belum |
| Modul Guru (materi, tugas, absensi, nilai) | Belum |
| Modul Siswa (tugas, kehadiran, nilai, kas, piket) | Belum |
| Ujian online (timer, auto-grading) | Belum |
| Modul Wali Kelas (cek nilai, notifikasi, rapor PDF, piket) | Belum |
| Modul Kas (bendahara, laporan) | Belum |
| Dashboard Kepala Sekolah (grafik, laporan) | Belum |
| Test hak akses per role | Belum |
| Tampilan (frontend) | Belum |

Perbarui tabel ini setiap ada tahap yang selesai.

---

## 8. Mengatasi masalah umum

| Pesan atau gejala | Penyebab dan solusi |
|---|---|
| `composer install` gagal, menyebut `ext-zip` | Aktifkan ekstensi `zip` di PHP, lalu buka terminal baru |
| `MissingAppKeyException: No application encryption key` | Jalankan `php artisan key:generate`, lalu `php artisan config:clear` |
| `Vite manifest not found` | Jalankan `npm install` lalu `npm run build` (atau `npm run dev`) |
| Aplikasi masih memakai SQLite atau database yang salah | Cek `DB_*` di `.env`, lalu `php artisan config:clear` dan `php artisan db:show` |
| `Unknown database 'learnedcpweb'` | Database belum dibuat (bagian 2.4) |
| `Access denied for user 'root'` | Periksa `DB_USERNAME` dan `DB_PASSWORD` di `.env` |
| `Base table or view already exists` | Jalankan `php artisan migrate:fresh --seed` |
| `Identifier name is too long` di migration | Beri nama manual pada index (argumen kedua `unique()` atau `index()`), maksimal 64 karakter |
| `git push` gagal: `Could not resolve host: github.com` | Masalah internet, ulangi `git push` setelah koneksi normal |
| Perubahan tampilan tidak muncul | Pastikan `npm run dev` berjalan, lalu refresh browser (Ctrl+F5) |
| `Class "App\Models\X" not found` | Model belum dibuat atau salah ketik nama |

Perintah yang sering dipakai:

```bash
php artisan migrate:status          # lihat migration mana yang sudah jalan
php artisan db:table <nama_tabel>   # lihat struktur satu tabel
php artisan model:show <NamaModel>  # lihat kolom dan relasi satu model
php artisan route:list              # lihat semua route
php artisan tinker                  # coba kode Laravel secara interaktif
```

---

## 9. Lisensi

Proyek ini dibuat untuk keperluan tugas kuliah dan dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).
