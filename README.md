# spinotek/certificate-generator

Package Laravel 12 untuk menerbitkan satu PDF sertifikat dari data peserta. Aplikasi pemakai memilih peserta dan memeriksa kelayakannya sebelum memanggil `issue()`. Setiap penerbitan menghasilkan kode unik dan QR yang membuka halaman verifikasi publik di domain penerbit.

## Mulai dalam 5 menit

Untuk aplikasi Laravel 12 dengan PHP 8.2+, DOM, GD, mbstring, dan database yang sudah terhubung.

1. **Pasang package dan jalankan migrasi.** Perintah Packagist berikut adalah target instalasi setelah Spinotek menerbitkan v0.1.0.

   ```sh
   composer require "spinotek/certificate-generator:^0.1.0"
   php artisan migrate
   ```

2. **Tentukan domain penerbit** di `.env` sebelum menerbitkan sertifikat. QR menyimpan URL ini secara permanen.

   ```dotenv
   APP_NAME="Akademi Contoh"
   APP_URL=https://akademi.example.org
   ```

3. **Beri akses editor** di `boot()` pada `App\Providers\AppServiceProvider`. Contoh ini memakai kolom boolean `is_admin` milik aplikasi. Sesuaikan dengan sistem hak akses Anda.

   ```php
   \Illuminate\Support\Facades\Gate::define('manage-certificate-design',
       fn (\App\Models\User $user) => (bool) $user->is_admin);
   ```

   Masuk sebagai admin dan buka `/certificates/design` pada domain aplikasi. Composer discovery memuat provider, migrasi, dan rute. Editor siap digunakan tanpa build frontend.

4. **Terbitkan dari model aplikasi.** Contoh ini mengasumsikan model pendaftaran memiliki relasi `participant`, `activity`, dan atribut tanggal `completed_at` yang di-cast ke `datetime`. Panggil setelah aplikasi memastikan kelayakan peserta.

   ```php
   use Spinotek\CertificateGenerator\CertificateGenerator;

   $certificate = app(CertificateGenerator::class)->issue([
       'participant_name' => $enrollment->participant->name,
       'activity_title'   => $enrollment->activity->title,
       'issued_date'      => $enrollment->completed_at->toDateString(),
   ]);

   return $certificate->download();
   ```

   Simpan `$certificate->code` untuk unduh ulang melalui `Spinotek\CertificateGenerator\Certificate::findOrFail($code)->download()` setelah aplikasi memeriksa izin unduh. Panggilan `issue()` berikutnya membuat terbitan baru. Nama penerbit berasal dari `APP_NAME` atau field opsional `issuer`.

### Jika aplikasi sudah memiliki rute sertifikat

Default editor adalah `/certificates/design`. Default verifikasi publik adalah `/certificates/verify/{code}`. Atur prefix secara terpisah di `.env` jika salah satunya sudah digunakan aplikasi:

```dotenv
CERTIFICATE_EDITOR_PREFIX=admin/piagam
CERTIFICATE_VERIFICATION_PREFIX=piagam/cek
```

Dengan pengaturan ini editor berada di `/admin/piagam`, QR baru membuka `/piagam/cek/{code}`, dan aplikasi dapat tetap melayani rute lamanya. Nilai prefix hanya berisi path. Untuk mengatur lewat file, jalankan `php artisan vendor:publish --tag=certificate-generator-config` dan edit `config/certificate-generator.php`. Jika aplikasi memakai cache, bangun ulang dengan `php artisan config:cache` lalu `php artisan route:cache`. Bila sudah ada PDF terbit dari package ini, pertahankan akses ke URL yang tercetak melalui rute kompatibilitas di aplikasi Anda.

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

## Instalasi lokal untuk pengembangan

Dari aplikasi pemakai, ganti `/path/ke/certificate-generator` dengan direktori checkout package:

```sh
composer config repositories.spinotek '{"type":"path","url":"/path/ke/certificate-generator","options":{"symlink":true,"versions":{"spinotek/certificate-generator":"dev-main"}}}'
composer require spinotek/certificate-generator:dev-main
php artisan migrate
```

## Template dan pengujian

Untuk mengganti template awal sebelum desain disimpan di editor, jalankan `php artisan vendor:publish --tag=certificate-template` di aplikasi pemakai, lalu ubah `resources/certificates/certificate.json`. Format versi 1 mendefinisikan ukuran halaman dan kotak setiap elemen dalam milimeter, diukur dari sudut kiri atas. Tiap elemen memiliki ID, tipe (`text`, `variable`, atau `qr`), `x`, `y`, `width`, dan `height`. Ukuran huruf dalam poin. Elemen teks menerima `font` (`sans`, `serif`, `mono`), `color` (`#RRGGBB`) dan `align` (`left`, `center`, `right`). Gunakan satu QR persegi dengan sisi minimal 30 mm. Atribut opsional `frame` dan `background` mengatur bingkai dan gambar latar. Editor menyimpan desain dan latar bersama di tabel `spinotek_certificate_designs`. Desain tersimpan diprioritaskan di atas template awal.

Jalankan `composer --working-dir=sandbox test` dari root dengan `pdftoppm` dan `pdftotext` di PATH (paket Poppler). Suite memakai SQLite dalam memori, merasterisasi PDF final, membaca QR dari gambar satu halaman penuh, dan mengakses URL hasil dekode melalui HTTP kernel Laravel.

## Lisensi

Kode Spinotek menggunakan [lisensi MIT](LICENSE). Anda dapat menggunakannya dalam aplikasi komersial dan closed-source. Sertakan pemberitahuan hak cipta dan lisensi MIT saat mendistribusikan kode package.

PDF dirender oleh Dompdf, yang dipasang sebagai dependensi Composer terpisah. Saat menyerahkan aplikasi beserta dependensinya, pertahankan pemberitahuan, lisensi dan kode sumber library LGPL yang didistribusikan, termasuk perubahan pada library tersebut. Pastikan penerima dapat mengganti library dengan versi kompatibel serta menggunakan hak yang diberikan LGPL. Font DejaVu menyertakan ketentuan Bitstream Vera dan, untuk sebagian glyph, Arev. Lihat [lisensi Dompdf](https://github.com/dompdf/dompdf/blob/master/LICENSE.LGPL) dan [pemberitahuan font](FONT-NOTICES.txt).
