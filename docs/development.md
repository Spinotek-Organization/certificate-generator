# Pengembangan package

Sandbox adalah aplikasi Laravel contoh di dalam repo ini. Gunakan untuk menguji perubahan pada package dengan SQLite terpisah. Untuk memasang package di aplikasi yang sudah ada, ikuti [panduan instalasi](installation.md).

## Jalankan sandbox

Dari root repository, siapkan sandbox Laravel dengan PHP 8.2+, Composer, GD, mbstring, dan PDO SQLite. Untuk instalasi pertama dengan Laravel Herd, jalankan:

```sh
cd sandbox
cp .env.example .env
touch database/database.sqlite
composer install
php artisan key:generate
php artisan migrate
herd link spinotek-demo
php artisan certificate-generator:demo
```

Perintah terakhir mencetak lokasi PDF dan URL verifikasinya. Buka PDF, pindai QR, lalu cocokkan nama **Rani Putri**, kegiatan **Lokakarya Desain Informasi**, penerbit **Spinotek Demo**, tanggal terbit, dan status **Valid**. Pada sandbox yang sudah disiapkan cukup ulangi `php artisan certificate-generator:demo`.

**Pindai dengan ponsel.** Domain Herd `.test` umumnya hanya dikenali komputer pengembang. Di Wi-Fi lokal yang sama, atur `APP_URL` di `sandbox/.env` ke alamat LAN komputer beserta port, misalnya `http://192.168.1.20:8000`. Jalankan `php artisan config:clear` lalu `php artisan serve --host=192.168.1.20 --port=8000` dari sandbox dengan IP komputer Anda. Di terminal kedua, terbitkan contoh baru. QR yang tercetak menyimpan alamat saat terbit.

## Akses admin sandbox

Di sandbox Herd, jalankan perintah berikut dari root repository:

```sh
php sandbox/artisan migrate
php sandbox/artisan certificate:admin
```

Buka tautan masuk yang dicetak dalam 10 menit. Sandbox membuka [editor desain](http://spinotek-demo.test/certificates/design) dan memberi sesi tersebut akses admin. Instalasi sandbox lama yang memakai `SESSION_DRIVER=array` perlu menggantinya dengan `SESSION_DRIVER=cookie` di `sandbox/.env`, lalu menjalankan `php sandbox/artisan config:clear`.

## Pasang checkout package di aplikasi lain

Dari aplikasi pemakai, ganti `/path/ke/certificate-generator` dengan direktori checkout package:

```sh
composer config repositories.spinotek '{"type":"path","url":"/path/ke/certificate-generator","options":{"symlink":true,"versions":{"spinotek/certificate-generator":"dev-main"}}}'
composer require spinotek/certificate-generator:dev-main
php artisan migrate
```

## Jalankan tes package

Jalankan `composer --working-dir=sandbox test` dari root dengan `pdftoppm` dan `pdftotext` di PATH (paket Poppler). Suite memakai SQLite dalam memori, merasterisasi PDF final, membaca QR dari gambar satu halaman penuh, dan mengakses URL hasil dekode melalui HTTP kernel Laravel.
