# Spinotek Certificate Generator

Buat sertifikat PDF dari data peserta di aplikasi Laravel dengan multi-template per kategori, gambar dasar sertifikat (upload 5 MiB / galeri preset), custom variables, nomor sertifikat fleksibel, serta verifikasi QR & pencarian manual.

## Instalasi cepat

Jalankan perintah di **folder aplikasi Laravel yang akan memakai package**, misalnya folder `spinotek-demo`. Pastikan aplikasi sudah mempunyai koneksi database dan ekstensi PHP DOM, GD, serta mbstring.

| Versi Laravel | PHP minimum |
| --- | --- |
| 12 | 8.2 |
| 13 | 8.3 |

Setelah `v0.2.0` tersedia di [Packagist](https://packagist.org/packages/spinotek/certificate-generator), jalankan dua perintah ini.

```bash
composer require "spinotek/certificate-generator:^0.2.0"
php artisan certificate-generator:install
```

Installer menyiapkan tabel Spinotek. Jika aplikasi sudah menjalankan `php artisan migrate` saat deploy, gunakan alur tersebut. Untuk mencoba sebelum rilis, lihat [instalasi dari GitHub](docs/installation.md#github-untuk-mencoba-dari-cabang-main). Pengguna `dev-main` dapat mengikuti [panduan beralih ke versi stabil](docs/installation.md#beralih-ke-versi-stabil).

### Coba PDF pertama

Pastikan `APP_URL` di `.env` adalah alamat yang dipakai untuk membuka aplikasi. Jalankan `php artisan config:clear` setelah mengubah alamat. Pada aplikasi lokal dengan database uji, jalankan perintah demo.

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
