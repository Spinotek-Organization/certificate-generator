# Spinotek Certificate Generator

Package Laravel untuk mendesain sertifikat, menerbitkan PDF dari data peserta, dan memverifikasi sertifikat melalui QR. Aplikasi kamu menentukan siapa yang boleh menerima dan mengunduh sertifikat.

- [Instalasi dan PDF pertama](#instalasi-dan-pdf-pertama)
- [Aktifkan editor desain](#aktifkan-editor-desain)
- [Hubungkan dengan data peserta](#hubungkan-dengan-data-peserta)
- [Pengaturan URL dan pemecahan masalah](#pengaturan-url-dan-pemecahan-masalah)

## Instalasi dan PDF pertama

| Aplikasi | PHP minimum |
| --- | --- |
| Laravel 11.54+ | PHP 8.2 |
| Laravel 12 | PHP 8.2 |
| Laravel 13 | PHP 8.3 |

Panduan ini berlaku mulai v0.2.0. Untuk aplikasi baru, gunakan versi Laravel yang masih menerima pembaruan keamanan. Lihat [jadwal dukungan Laravel](https://laravel.com/docs/12.x/releases#support-policy). Siapkan Composer, koneksi database, dan ekstensi PHP DOM, GD, serta mbstring. Jalankan perintah dari root aplikasi Laravel yang akan memakai package.

### 1. Pasang package

Setelah v0.2.0 tersedia di Packagist, jalankan:

```bash
composer require "spinotek/certificate-generator:^0.2.0"
php artisan migrate
```

Jika rilis tersedia sebagai tag di GitHub, tambahkan sumber GitHub ke Composer sebelum menjalankan perintah di atas. Composer akan memilih tag yang sesuai dengan versi Laravel aplikasi.

```bash
composer config repositories.spinotek vcs https://github.com/Spinotek-Organization/certificate-generator
```

Laravel package discovery mendaftarkan command, rute, view, dan migrasi. Migrasi membuat tabel desain dan penerbitan sertifikat. Editor membawa CSS dan JavaScript sendiri sehingga dapat digunakan pada aplikasi Tailwind maupun Bootstrap.

### 2. Atur alamat verifikasi

Sebelum menerbitkan PDF, isi `APP_URL` dengan alamat yang akan dipakai untuk membuka aplikasi. Sertakan subfolder jika aplikasi disajikan di bawah subfolder. Gunakan URL yang bisa diakses penerima sertifikat.

```dotenv
APP_URL=https://akademi.example.org
```

Ganti alamat contoh dengan domain aplikasi. Di Herd lokal, gunakan alamat seperti `http://akademi.test`. Jalankan `php artisan config:clear` setelah mengubahnya. QR menyimpan alamat yang berlaku saat PDF pertama kali diterbitkan.

### 3. Terbitkan dan verifikasi PDF contoh

Jalankan di aplikasi lokal, dengan `APP_ENV=local` atau `testing` dan database uji.

```bash
php artisan certificate-generator:demo
```

Perintah mencetak lokasi PDF di `storage/app/certificates/` dan URL verifikasinya. Buka PDF, pindai QR atau buka tautan yang dicetak. Halaman verifikasi seharusnya menampilkan nama **Rani Putri**, kegiatan **Lokakarya Desain Informasi**, dan status **Valid**. Setiap eksekusi membuat satu terbitan contoh di database. Jika menguji lewat kamera ponsel, gunakan alamat aplikasi yang juga dapat diakses ponsel.

## Aktifkan editor desain

Gunakan autentikasi yang sudah ada di aplikasi. Definisikan Gate `manage-certificate-design` sekali saja di method `boot()` pada `app/Providers/AppServiceProvider.php`. Contoh ini mengikuti aplikasi yang mempunyai kolom boolean `is_admin`. Ganti pemeriksaannya dengan role atau permission yang dipakai aplikasi kamu.

```php
use App\Models\User;
use Illuminate\Support\Facades\Gate;

// Di dalam method boot().
Gate::define('manage-certificate-design', fn (User $user): bool => (bool) $user->is_admin);
```

Masuk sebagai admin dan buka `/certificates/design` pada domain aplikasi. Tautan menu bisa ditambahkan ke layout admin melalui nama rutenya:

```blade
@can('manage-certificate-design')
    <a href="{{ route('spinotek.design.edit') }}">Desain Sertifikat</a>
@endcan
```

Unggah JPG atau PNG (maksimal 1 MiB dan 9 megapiksel), lalu atur posisi, ukuran, font, warna, dan perataan teks. Admin juga bisa memindahkan dan mengubah ukuran QR. Pilih **Preview PDF** untuk melihat draf, lalu **Simpan desain** untuk memakai perubahan pada penerbitan berikutnya. Aplikasi yang menerbitkan lewat kode dapat langsung memakai template bawaan tanpa pengaturan Gate editor.

## Hubungkan dengan data peserta

Panggil `issue()` dari controller, service, atau job aplikasi setelah mengecek bahwa peserta berhak menerima sertifikat. Contoh ini memakai model `Enrollment` dengan relasi `participant` dan `activity`, kolom kelulusan `completed_at`, dan policy `view` untuk memeriksa kepemilikan pendaftaran. Sesuaikan nama model, relasi, dan aturan kelulusan dengan aplikasi kamu.

1. Tambahkan kolom UUID nullable `certificate_code` pada tabel pendaftaran milik aplikasi, lalu jalankan migrasinya. Kolom menyimpan kode dari Spinotek untuk unduh ulang.
2. Daftarkan rute unduh dengan middleware `auth` dan arahkan ke controller berikut. Rute ini dapat dipanggil dari tombol **Unduh Sertifikat**.

```php
// routes/web.php
use App\Http\Controllers\DownloadCertificateController;
use Illuminate\Support\Facades\Route;

Route::get('/enrollments/{enrollment}/certificate', DownloadCertificateController::class)
    ->middleware('auth')
    ->name('enrollments.certificate');
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spinotek\CertificateGenerator\Certificate;
use Spinotek\CertificateGenerator\CertificateGenerator;

class DownloadCertificateController
{
    public function __invoke(Enrollment $enrollment, CertificateGenerator $generator): Response
    {
        $certificate = DB::transaction(function () use ($enrollment, $generator): Certificate {
            $enrollment = Enrollment::query()->lockForUpdate()->findOrFail($enrollment->getKey());
            Gate::authorize('view', $enrollment);
            abort_unless($enrollment->completed_at, 403);

            if ($enrollment->certificate_code !== null) {
                return Certificate::query()->findOrFail($enrollment->certificate_code);
            }

            $certificate = $generator->issue([
                'participant_name' => $enrollment->participant->name,
                'activity_title' => $enrollment->activity->title,
                'issued_date' => today()->toDateString(),
                'issuer' => 'Akademi Contoh',
            ]);

            $enrollment->certificate_code = $certificate->code;
            $enrollment->save();

            return $certificate;
        }, attempts: 3);

        return $certificate->download();
    }
}
```

`issuer` opsional dan secara bawaan memakai `APP_NAME`. Masukkan tanggal terbit dalam format `YYYY-MM-DD`. Jika penerbitan dilakukan di job, simpan kode terbitan pada data peserta dengan cara yang sama. Setiap pemanggilan `issue()` membuat PDF dan kode baru, sedangkan unduh ulang dari kode tersimpan memberikan file yang sama. Untuk mengirim tautan verifikasi terbitan baru, gunakan `$generator->verificationUrl($certificate->code)`.

## Pengaturan URL dan pemecahan masalah

Secara bawaan, editor berada di `/certificates/design` dan verifikasi publik di `/certificates/verify/{code}`. Jika rute tersebut sudah dipakai aplikasi, atur prefix di `.env`. Gunakan hanya path.

```dotenv
CERTIFICATE_EDITOR_PREFIX=admin/piagam
CERTIFICATE_VERIFICATION_PREFIX=piagam/cek
```

Contoh ini menghasilkan `/admin/piagam` dan `/piagam/cek/{code}`. Untuk memublikasikan konfigurasi ke folder aplikasi, gunakan `php artisan vendor:publish --tag=certificate-generator-config`. Jika memakai cache Laravel, bangun ulang cache konfigurasi dan rute setelah mengubah prefix. Pertahankan rute lama untuk QR yang sudah diterbitkan.

| Gejala | Yang perlu diperiksa |
| --- | --- |
| Composer gagal menemukan versi yang cocok | Cocokkan versi PHP dan Laravel dengan batas versi pada tag rilis, lalu periksa sumber Composer yang dipakai. |
| Editor 403 | Masuk dengan akun yang lulus Gate `manage-certificate-design`. |
| Editor 404 | Cek alamat dengan `php artisan route:list --name=spinotek`. Gunakan domain aplikasi dan subfolder hanya jika ada pada URL yang aktif. |
| Tabel Spinotek belum ada | Cek koneksi database, lalu jalankan `php artisan migrate`. |
| QR PDF mengarah ke `localhost` atau domain lama | Cek `APP_URL`, jalankan `php artisan config:clear`, lalu buat terbitan uji baru. PDF yang sudah terbit menyimpan alamat awal. |

## Untuk pengembangan package

### Jalankan sandbox

Dari root repository, siapkan sandbox Laravel dengan PHP 8.2+, Composer, GD, mbstring, dan PDO SQLite. Untuk instalasi pertama dengan Laravel Herd, jalankan:

```sh
cd sandbox
cp .env.example .env
touch database/database.sqlite
composer install
php artisan key:generate
php artisan migrate
herd link sertifikat-sandbox
php artisan certificate-generator:demo
```

Perintah terakhir mencetak lokasi PDF dan URL verifikasinya. Buka PDF, pindai QR, lalu cocokkan nama **Rani Putri**, kegiatan **Lokakarya Desain Informasi**, penerbit **Akademi Contoh**, tanggal terbit, dan status **Valid**. Pada sandbox yang sudah disiapkan cukup ulangi `php artisan certificate-generator:demo`.

**Pindai dengan ponsel.** Domain Herd `.test` umumnya hanya dikenali komputer pengembang. Di Wi-Fi lokal yang sama, atur `APP_URL` di `sandbox/.env` ke alamat LAN komputer beserta port, misalnya `http://192.168.1.20:8000`. Jalankan `php artisan config:clear` lalu `php artisan serve --host=192.168.1.20 --port=8000` dari sandbox dengan IP komputer Anda. Di terminal kedua, terbitkan contoh baru. QR yang tercetak menyimpan alamat saat terbit.

### Akses admin sandbox

Di sandbox Herd, jalankan perintah berikut dari root repository:

```sh
php sandbox/artisan migrate
php sandbox/artisan certificate:admin
```

Buka tautan masuk yang dicetak dalam 10 menit. Sandbox membuka [editor desain](http://sertifikat-sandbox.test/certificates/design) dan memberi sesi tersebut akses admin. Instalasi sandbox lama yang memakai `SESSION_DRIVER=array` perlu menggantinya dengan `SESSION_DRIVER=cookie` di `sandbox/.env`, lalu menjalankan `php sandbox/artisan config:clear`.

### Pasang checkout package di aplikasi lain

Dari aplikasi pemakai, ganti `/path/ke/certificate-generator` dengan direktori checkout package:

```sh
composer config repositories.spinotek '{"type":"path","url":"/path/ke/certificate-generator","options":{"symlink":true,"versions":{"spinotek/certificate-generator":"dev-main"}}}'
composer require spinotek/certificate-generator:dev-main
php artisan migrate
```

### Template dan pengujian

Untuk mengganti template awal sebelum desain disimpan di editor, jalankan `php artisan vendor:publish --tag=certificate-template` di aplikasi pemakai, lalu ubah `resources/certificates/certificate.json`. Format versi 1 mendefinisikan ukuran halaman dan kotak setiap elemen dalam milimeter, diukur dari sudut kiri atas. Tiap elemen memiliki ID, tipe (`text`, `variable`, atau `qr`), `x`, `y`, `width`, dan `height`. Ukuran huruf dalam poin. Elemen teks menerima `font` (`sans`, `serif`, `mono`), `color` (`#RRGGBB`) dan `align` (`left`, `center`, `right`). Gunakan satu QR persegi dengan sisi minimal 30 mm. Atribut opsional `frame` dan `background` mengatur bingkai dan gambar latar. Editor menyimpan desain dan latar bersama di tabel `spinotek_certificate_designs`. Desain tersimpan diprioritaskan di atas template awal.

Jalankan `composer --working-dir=sandbox test` dari root dengan `pdftoppm` dan `pdftotext` di PATH (paket Poppler). Suite memakai SQLite dalam memori, merasterisasi PDF final, membaca QR dari gambar satu halaman penuh, dan mengakses URL hasil dekode melalui HTTP kernel Laravel.

## Lisensi

Kode Spinotek menggunakan [lisensi MIT](LICENSE). Anda dapat menggunakannya dalam aplikasi komersial dan closed-source. Sertakan pemberitahuan hak cipta dan lisensi MIT saat mendistribusikan kode package.

PDF dirender oleh Dompdf, yang dipasang sebagai dependensi Composer terpisah. Saat menyerahkan aplikasi beserta dependensinya, pertahankan pemberitahuan, lisensi dan kode sumber library LGPL yang didistribusikan, termasuk perubahan pada library tersebut. Pastikan penerima dapat mengganti library dengan versi kompatibel serta menggunakan hak yang diberikan LGPL. Font DejaVu menyertakan ketentuan Bitstream Vera dan, untuk sebagian glyph, Arev. Lihat [lisensi Dompdf](https://github.com/dompdf/dompdf/blob/master/LICENSE.LGPL) dan [pemberitahuan font](FONT-NOTICES.txt).
