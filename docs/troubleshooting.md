# Pemecahan masalah

[Instalasi](installation.md) · [Editor desain](editor.md) · [Integrasi Laravel](integration.md)

| Gejala | Yang perlu diperiksa |
| --- | --- |
| Composer menulis `it could not be found in any version` | Pastikan nama package `spinotek/certificate-generator`. Jika `v0.2.0` belum terindeks di Packagist, gunakan [cara GitHub](installation.md#github-untuk-mencoba-dari-cabang-main). |
| Composer menemukan package tetapi menolak versi Laravel | Jalankan `php artisan --version`. Laravel 11 memerlukan minimal 11.54 dan versi package yang mendukungnya. Tag `v0.1.0` ditujukan untuk Laravel 12. |
| Command install atau demo tidak ditemukan | Jalankan `composer show spinotek/certificate-generator`. Cocokkan versinya dengan [panduan instalasi](installation.md). Pastikan package discovery aktif dan jalankan `php artisan package:discover` jika diperlukan. |
| Command demo ditolak | Jalankan demo pada aplikasi `APP_ENV=local` atau `testing`. Untuk alur resmi, gunakan integrasi yang mengambil data peserta aplikasi. |
| Tabel Spinotek belum tersedia | Periksa koneksi database, lalu jalankan `php artisan certificate-generator:install` atau migrasi standar aplikasi. |
| Editor 403 | Login dengan akun yang lulus Gate `manage-certificate-design`. Periksa role atau permission yang dicek. |
| Editor 404 | Jalankan `php artisan route:list --name=spinotek`. Cocokkan prefix dan domain. Gunakan subfolder seperti `/lms` hanya jika aplikasi disajikan di subfolder itu. |
| Menu editor belum terlihat | Tambahkan [tautan Blade](editor.md#buka-editor-dari-panel-admin) ke sidebar atau layout admin aplikasi. |
| Unggah gambar atau simpan desain gagal | Gunakan JPG/PNG dalam batas ukuran [editor](editor.md#atur-dan-uji-desain). Jika menerima HTTP 413, periksa batas ukuran request pada PHP dan web server. |
| QR PDF mengarah ke `localhost` atau domain lama | Perbaiki `APP_URL`, jalankan `php artisan config:clear`, lalu terbitkan satu PDF uji baru. Unduhan ulang mengembalikan PDF asli dengan QR yang sudah tersimpan. |
| QR pada hasil Preview PDF membuka editor | Gunakan hasil `certificate-generator:demo` atau sertifikat yang diterbitkan lewat `issue()` untuk menguji QR verifikasi publik. |
| QR bisa dibuka di Mac tetapi gagal dari ponsel | Pastikan domain bisa diakses dari ponsel. Domain Herd `.test` umumnya hanya terpasang di komputer pengembang. |
| Label penerbit bertuliskan Laravel | Ubah `APP_NAME` atau kirim `issuer` saat menerbitkan. Nilai baru muncul pada terbitan berikutnya. |
| Peserta tidak dapat mengunduh PDF | Periksa izin, kelayakan, dan rute unduh pada aplikasi. Gate editor hanya mengatur akses desain. Jika sebuah tombol berwarna abu-abu, periksa aturan yang mengaktifkan tombol di aplikasi. |

## Sesuaikan URL editor dan verifikasi

Secara bawaan, editor ada di `/certificates/design` dan verifikasi di `/certificates/verify/{code}`. Jika aplikasi sudah memakai salah satu rute itu, ubah prefix yang bentrok di `.env`. Gunakan path saja:

```dotenv
CERTIFICATE_EDITOR_PREFIX=admin/piagam
CERTIFICATE_VERIFICATION_PREFIX=piagam/cek
```

Contoh ini menghasilkan `/admin/piagam` untuk editor dan `/piagam/cek/{code}` untuk verifikasi. Kata `piagam` hanya contoh nama path. Untuk menyalin konfigurasi package ke aplikasi, jalankan:

```bash
php artisan vendor:publish --tag=certificate-generator-config
```

Setelah mengubah prefix, jalankan:

```bash
php artisan config:clear
php artisan route:clear
php artisan route:list --name=spinotek
```

Di server yang memakai cache, bangun ulang cache konfigurasi dan rute sesuai alur deploy aplikasi. Jika ada QR yang sudah beredar, pertahankan URL lama agar tautan yang tercetak tetap dapat dibuka.
