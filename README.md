# Spinotek Certificate Generator

Buat sertifikat PDF dari data peserta di aplikasi Laravel. Pilih palet, tambahkan logo dan tanda tangan, lalu pindai QR pada PDF untuk membuka halaman verifikasi.

## Instalasi cepat

Jalankan perintah di **folder aplikasi Laravel yang akan memakai package**, misalnya folder `spinotek-demo`. Pastikan aplikasi sudah mempunyai koneksi database dan ekstensi PHP DOM, GD, serta mbstring.

| Versi Laravel | PHP minimum |
| --- | --- |
| 11.54 atau lebih baru dalam seri 11 | 8.2 |
| 12 | 8.2 |
| 13 | 8.3 |

### 1. Pilih sumber package

**Jika `v0.2.0` sudah tersedia di Packagist**, jalankan dari aplikasi Laravel:

```bash
composer require "spinotek/certificate-generator:^0.2.0"
```

**Untuk uji coba dari cabang `main`**, gunakan repository GitHub:

```bash
composer config repositories.spinotek vcs https://github.com/Spinotek-Organization/certificate-generator
composer require "spinotek/certificate-generator:dev-main"
```

Jika sebelumnya memakai `dev-main`, ikuti [cara beralih ke versi stabil](docs/installation.md#beralih-ke-versi-stabil).

### 2. Siapkan database dan alamat aplikasi

Di `.env`, isi `APP_URL` dengan alamat aplikasi yang benar, misalnya `http://spinotek-demo.test` untuk Herd lokal atau `https://sertifikat.spinotek.example` untuk contoh domain publik. Setelah mengubah `.env`, bersihkan cache konfigurasi dan siapkan tabel Spinotek:

```bash
php artisan config:clear
php artisan certificate-generator:install
```

Jika aplikasi sudah punya alur deploy, `php artisan migrate` juga menjalankan migrasi Spinotek.

### 3. Coba PDF pertama

Pada aplikasi lokal dengan database uji, jalankan:

```bash
php artisan certificate-generator:demo
```

Terminal mencetak lokasi PDF dan URL verifikasi. Buka PDF lalu pindai QR. Halaman verifikasi akan menampilkan contoh **Rani Putri**, **Lokakarya Desain Informasi**, dan status **Valid**. [Panduan instalasi dan uji pertama](docs/installation.md)

Untuk mengubah desain, [atur izin editor](docs/editor.md#beri-akses-admin), lalu buka `/certificates/design`. Untuk menerbitkan dari data peserta dan menambahkan tombol unduh, ikuti [contoh integrasi Laravel](docs/integration.md).

## Panduan penggunaan

| Saya ingin | Panduan |
| --- | --- |
| Mengatur izin admin, desain, dan menu editor | [Editor desain](docs/editor.md) |
| Menambahkan tombol unduh dan menghubungkan data peserta | [Integrasi Laravel](docs/integration.md) |
| Memahami penyimpanan, rute verifikasi, dan file yang sudah terbit | [Integrasi Laravel](docs/integration.md#verifikasi-dan-penyimpanan) |
| Mengatasi error Composer, migrasi, editor, atau QR | [Pemecahan masalah](docs/troubleshooting.md) |
| Menyiapkan sandbox atau menjalankan tes package | [Pengembangan package](docs/development.md) |

## Lisensi

Kode Spinotek menggunakan [lisensi MIT](LICENSE). Anda dapat menggunakannya dalam aplikasi komersial dan closed-source. Sertakan pemberitahuan hak cipta dan lisensi MIT saat mendistribusikan kode package.

PDF dirender oleh Dompdf, yang dipasang sebagai dependensi Composer terpisah. Saat menyerahkan aplikasi beserta dependensinya, pertahankan pemberitahuan, lisensi dan kode sumber library LGPL yang didistribusikan, termasuk perubahan pada library tersebut. Pastikan penerima dapat mengganti library dengan versi kompatibel serta menggunakan hak yang diberikan LGPL. Font DejaVu menyertakan ketentuan Bitstream Vera dan, untuk sebagian glyph, Arev. Lihat [lisensi Dompdf](https://github.com/dompdf/dompdf/blob/master/LICENSE.LGPL) dan [pemberitahuan font](FONT-NOTICES.txt).
