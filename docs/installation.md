# Instalasi dan PDF pertama

[README](../README.md) · [Editor desain](editor.md) · [Integrasi Laravel](integration.md) · [Pemecahan masalah](troubleshooting.md)

## 1. Periksa aplikasi

Buka Terminal dari folder aplikasi Laravel yang akan memakai sertifikat. Folder itu mempunyai `artisan` dan `composer.json`. Gunakan PHP 8.2 atau yang lebih baru untuk Laravel 11.54 dan Laravel 12, serta PHP 8.3 atau yang lebih baru untuk Laravel 13. Untuk aplikasi baru, lihat [jadwal dukungan Laravel](https://laravel.com/docs/12.x/releases#support-policy).

```bash
php -v
php artisan --version
composer --version
php -m
```

Aktifkan `dom`, `gd`, `mbstring`, dan driver PDO sesuai database aplikasi, misalnya `pdo_mysql` atau `pdo_sqlite`. Pastikan aplikasi sudah berjalan dengan `APP_KEY` dan `storage/` bisa ditulis. Editor memuat asetnya sendiri dan dapat dipakai bersama Tailwind maupun Bootstrap tanpa langkah build tambahan.

## 2. Pilih satu cara instalasi

### GitHub untuk mencoba sekarang

```bash
composer config repositories.spinotek vcs https://github.com/Spinotek-Organization/certificate-generator
composer require "spinotek/certificate-generator:dev-main"
```

Composer mengambil cabang `main` dari repo publik. Perintah ini cocok untuk uji lokal. Gunakan tag stabil saat memasang di server.

### Packagist untuk rilis stabil

Jalankan ini ketika `v0.2.0` sudah tersedia di [Packagist](https://packagist.org/packages/spinotek/certificate-generator):

```bash
composer require "spinotek/certificate-generator:^0.2.0"
```

### Tag GitHub untuk penggunaan tanpa Packagist

Jika tag `v0.2.0` sudah ada di [GitHub](https://github.com/Spinotek-Organization/certificate-generator/tags), gunakan:

```bash
composer config repositories.spinotek vcs https://github.com/Spinotek-Organization/certificate-generator
composer require "spinotek/certificate-generator:^0.2.0"
```

GitHub `v0.1.0` adalah versi awal untuk Laravel 12. Instruksi command demo dan matriks kompatibilitas pada panduan ini berlaku untuk `v0.2.0` atau cabang `main` yang memuat perubahan tersebut.

## 3. Jalankan migrasi

Periksa database yang dituju aplikasi. Jika baru mengubah pengaturan database di `.env`, jalankan `php artisan config:clear` lebih dulu. Lalu jalankan:

```bash
php artisan migrate
composer show spinotek/certificate-generator
php artisan route:list --name=spinotek
```

Perintah migrasi standar menjalankan semua migrasi yang tertunda, termasuk milik aplikasi. Jika sedang uji coba dan hanya ingin membuat tabel Spinotek, gunakan ini sebagai pengganti perintah migrasi standar:

```bash
php artisan migrate --path=vendor/spinotek/certificate-generator/database/migrations
```

Composer discovery memuat service provider, rute, migrasi, dan command. Migrasi membuat tabel `spinotek_certificate_designs` untuk desain dan `spinotek_certificates` untuk terbitan.

## 4. Atur alamat dan nama penerbit

Isi `APP_URL` di `.env` sesuai alamat yang dipakai penerima untuk mengakses aplikasi. `APP_NAME` menjadi nama penerbit bawaan. Aplikasi bisa mengirim nama penerbit berbeda lewat parameter `issuer` saat menerbitkan.

```dotenv
APP_URL=https://sertifikat.spinotek.example
APP_NAME="Spinotek Demo"
```

```bash
php artisan config:clear
```

Sebagai contoh, gunakan `http://spinotek-demo.test` jika itu alamat Herd yang aktif. Sertakan subfolder seperti `/lms` hanya jika aplikasi benar-benar disajikan di alamat tersebut. PDF menyimpan alamat saat terbit, jadi tetapkan alamat yang bisa dibuka penerima sebelum menerbitkan sertifikat resmi.

## 5. Buat dan verifikasi PDF

Dengan `APP_ENV=local` atau `testing` dan database uji, jalankan:

```bash
php artisan certificate-generator:demo
```

1. Buka file PDF dari path yang tercetak di Terminal. File disimpan di `storage/app/certificates/`.
2. Pindai QR dari file itu. Untuk memeriksa halaman dari komputer yang sama, kamu juga bisa membuka URL verifikasi yang tercetak di Terminal.
3. Cocokkan **Rani Putri**, **Lokakarya Desain Informasi**, dan status **Valid**. Alamat dari QR seharusnya sama dengan alamat yang dicetak command.

Setiap eksekusi membuat satu sertifikat contoh di database. Domain lokal `.test` umumnya bisa dibuka dari komputer pengembang. Jika ingin menguji dengan ponsel, terbitkan dari alamat staging atau alamat jaringan lokal yang juga bisa diakses ponsel.

Setelah uji ini berhasil, [aktifkan editor](editor.md) jika perlu mengubah desain, kemudian [hubungkan data peserta](integration.md).

## Beralih ke versi stabil

Setelah `v0.2.0` tersedia di Packagist, aplikasi yang memakai checkout lokal atau GitHub dapat memindahkan sumbernya:

```bash
composer config --unset repositories.spinotek
composer require "spinotek/certificate-generator:^0.2.0"
php artisan migrate
```

Jika nama repo Composer sebelumnya berbeda, gunakan nama yang ada pada `composer.json`. Commit `composer.json` dan `composer.lock` aplikasi dengan sumber dan versi yang dapat diakses server serta anggota tim.

## Uji sebelum memakai data peserta

Di staging, gunakan URL HTTPS yang bisa dibuka penerima. Jalankan migrasi sesuai alur deploy aplikasi. Beri akun admin akses editor jika diperlukan, lalu terbitkan satu sertifikat melalui alur aplikasi dengan data uji. Periksa bahwa unduhan menampilkan data yang benar, QR dapat dibuka tanpa masuk akun, dan unduh ulang menghasilkan PDF yang sama.
