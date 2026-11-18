# spinotek/certificate-generator

Package Laravel 12 untuk menerbitkan satu PDF sertifikat dari data peserta. Aplikasi pemakai memilih peserta dan memeriksa kelayakannya sebelum memanggil `issue()`. Setiap penerbitan menghasilkan kode unik dan QR yang membuka halaman verifikasi publik di domain penerbit.

## Coba di sandbox lokal

Dari root repository, siapkan sandbox Laravel dengan PHP 8.2+, Composer, GD, mbstring, dan PDO SQLite. Untuk instalasi pertama dengan Laravel Herd, jalankan:

```sh
cd sandbox
cp .env.example .env
touch database/database.sqlite
composer install
php artisan key:generate
php artisan migrate
herd link sertifikat-sandbox
php artisan certificate:demo
```

Perintah terakhir mencetak lokasi PDF dan URL verifikasinya. Buka PDF, pindai QR, lalu cocokkan nama **Rani Putri**, kegiatan **Lokakarya Desain Informasi**, penerbit **Akademi Contoh**, tanggal terbit, dan status **Valid**. Pada sandbox yang sudah disiapkan cukup ulangi `php artisan certificate:demo`.

**Pindai dengan ponsel.** Domain Herd `.test` umumnya hanya dikenali komputer pengembang. Di Wi-Fi lokal yang sama, atur `APP_URL` di `sandbox/.env` ke alamat LAN komputer beserta port, misalnya `http://192.168.1.20:8000`. Jalankan `php artisan config:clear` lalu `php artisan serve --host=192.168.1.20 --port=8000` dari sandbox dengan IP komputer Anda. Di terminal kedua, terbitkan contoh baru. QR yang tercetak menyimpan alamat saat terbit.

## Editor desain

Di sandbox Herd, jalankan perintah berikut dari root repository:

```sh
php sandbox/artisan migrate
php sandbox/artisan certificate:admin
```

Buka tautan masuk yang dicetak dalam 10 menit. Sandbox membuka [editor desain](http://sertifikat-sandbox.test/certificates/design) dan memberi sesi tersebut akses admin. Instalasi sandbox lama yang memakai `SESSION_DRIVER=array` perlu menggantinya dengan `SESSION_DRIVER=cookie` di `sandbox/.env`, lalu menjalankan `php sandbox/artisan config:clear`.

Pilih JPG/PNG untuk latar (hingga 1 MiB dan 9 megapiksel). Latar mengisi halaman. Matikan **Tampilkan bingkai** bila latar sudah memiliki bingkai. Pilih elemen di kanvas atau daftar, lalu geser atau masukkan X/Y dalam mm. Kontrol elemen tersedia untuk teks tetap dan variabel peserta. Untuk QR, tarik sudut kanan bawah elemen terpilih atau atur **Sisi QR (mm)**. **Preview PDF** merender draf dengan data Rani Putri. QR pada preview membuka editor. **Simpan desain** mengaktifkan desain untuk penerbitan berikutnya. Jalankan `php sandbox/artisan certificate:demo` untuk mendapatkan PDF baru dengan QR verifikasi resminya.

## Pasang di aplikasi Laravel 12

Dari direktori aplikasi pemakai, sesuaikan path repository lokal:

```sh
composer config repositories.spinotek '{"type":"path","url":"/Users/nazar/Sites/certificate-generator","options":{"symlink":true,"versions":{"spinotek/certificate-generator":"dev-main"}}}'
composer require spinotek/certificate-generator:dev-main
php artisan migrate
```

Composer discovery mendaftarkan provider, migrasi, dan rute verifikasi. Atur `APP_URL` ke URL dasar aplikasi yang dapat dijangkau pemindai. `APP_NAME` menjadi nama penerbit bawaan.

```php
use Spinotek\CertificateGenerator\CertificateGenerator;

$certificate = app(CertificateGenerator::class)->issue([
    'participant_name' => 'Rani Putri',
    'activity_title' => 'Lokakarya Desain Informasi',
    'issued_date' => '2026-11-18',
    'issuer' => 'Akademi Contoh', // Opsional, menggunakan APP_NAME jika dihilangkan.
]);

return $certificate->download();
```

Simpan `$certificate->code` untuk unduhan berikutnya. Dari controller aplikasi yang memeriksa izin unduh, kembalikan `Spinotek\CertificateGenerator\Certificate::findOrFail($code)->download()`. Tabel `spinotek_certificates` menyimpan identitas terbitan dan PDF dalam satu baris. Unduhan membaca byte PDF asli. Memanggil `issue()` kembali akan membuat terbitan baru.

### Hak akses editor di aplikasi pemakai

Definisikan Gate `manage-certificate-design` di `boot()` provider aplikasi sesuai otorisasi Anda. Contoh untuk aplikasi yang memiliki kolom boolean `is_admin`:

```php
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Gate::define('manage-certificate-design', fn (User $user) => (bool) $user->is_admin);
```

Keempat rute editor menggunakan middleware `web` dan Gate tersebut. Admin yang sudah masuk dapat membuka `/certificates/design`. Blade memuat CSS dan JavaScript bawaan secara langsung. Saat upgrade dari milestone 1, jalankan `php artisan migrate` untuk menambahkan tabel desain.

## Template dan pengujian

Untuk mengganti template awal sebelum desain disimpan di editor, jalankan `php artisan vendor:publish --tag=certificate-template` di aplikasi pemakai, lalu ubah `resources/certificates/certificate.json`. Format versi 1 mendefinisikan ukuran halaman dan kotak setiap elemen dalam milimeter, diukur dari sudut kiri atas. Tiap elemen memiliki ID, tipe (`text`, `variable`, atau `qr`), `x`, `y`, `width`, dan `height`. Ukuran huruf dalam poin. Elemen teks menerima `font` (`sans`, `serif`, `mono`), `color` (`#RRGGBB`) dan `align` (`left`, `center`, `right`). Gunakan satu QR persegi dengan sisi minimal 30 mm. Atribut opsional `frame` dan `background` mengatur bingkai dan gambar latar. Editor menyimpan desain dan latar bersama di tabel `spinotek_certificate_designs`. Desain tersimpan diprioritaskan di atas template awal.

Jalankan `composer --working-dir=sandbox test` dari root dengan `pdftoppm` dan `pdftotext` di PATH (paket Poppler). Suite memakai SQLite dalam memori, merasterisasi PDF final, membaca QR dari gambar satu halaman penuh, dan mengakses URL hasil dekode melalui HTTP kernel Laravel.
