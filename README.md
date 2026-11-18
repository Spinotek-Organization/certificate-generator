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

## Template dan pengujian

Untuk mengedit template, jalankan `php artisan vendor:publish --tag=certificate-template` di aplikasi pemakai, lalu ubah `resources/certificates/certificate.json`. Format versi 1 mendefinisikan ukuran halaman dan kotak setiap elemen dalam milimeter, diukur dari sudut kiri atas. Tiap elemen memiliki ID, tipe (`text`, `variable`, atau `qr`), `x`, `y`, `width`, dan `height`. Ukuran huruf dalam poin. Gunakan satu QR persegi dengan sisi minimal 30 mm.

Jalankan `composer --working-dir=sandbox test` dari root dengan `pdftoppm` dan `pdftotext` di PATH (paket Poppler). Suite memakai SQLite dalam memori, merasterisasi PDF final, membaca QR dari gambar satu halaman penuh, dan mengakses URL hasil dekode melalui HTTP kernel Laravel.
