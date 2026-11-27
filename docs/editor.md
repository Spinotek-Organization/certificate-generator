# Editor desain (Certificate Creator)

[Instalasi](installation.md) · [Integrasi Laravel](integration.md) · [Pemecahan masalah](troubleshooting.md)

## Beri akses admin

Di `app/Providers/AppServiceProvider.php` aplikasi, tambahkan import yang masih diperlukan:

```php
use App\Models\User;
use Illuminate\Support\Facades\Gate;
```

Di dalam method `boot()` yang sudah ada, tambahkan Gate sesuai aturan akses aplikasi. Contoh ini mengasumsikan model `User` punya kolom boolean `is_admin`:

```php
Gate::define('manage-certificate-design', fn (User $user): bool => (bool) $user->is_admin);
```

Jika aplikasi memakai Spatie Laravel Permission dengan role `admin` dan `super admin`, gunakan ini sebagai penggantinya. Sesuaikan nama role dengan aplikasi:

```php
Gate::define('manage-certificate-design', fn (User $user): bool => $user->hasAnyRole(['admin', 'super admin']));
```

## Buka Certificate Creator dari panel admin

Masuk sebagai admin, lalu buka `/certificates/design` pada domain aplikasi. Untuk menampilkan menu di sidebar Blade, sisipkan:

```blade
@can('manage-certificate-design')
    <a href="{{ route('spinotek.design.index') }}">Desain Sertifikat</a>
@endcan
```

`/certificates/design` menampilkan daftar seluruh template sertifikat per kategori (`general`, `event`, `course`, atau kategori kustom di `config/certificate-generator.php`), sedangkan `/certificates/design/editor/{design?}` membuka workspace editor drag-and-drop.

## Buat & kelola multi-template

1. Pada halaman **Certificate Creator** (`/certificates/design`), isi nama template, pilih kategori (`Umum`, `Event`, `E-Course`, dll.), format nomor (mis. `CERT/{CAT}/{YYYY}/{SEQ:5}`), serta mode awal:
   - **Kanvas Bersih (Blank Mode)**: hanya berisi 1 elemen QR, cocok bila ingin memakai **Gambar Dasar Sertifikat** hasil desain Canva/Figma/Photoshop.
   - **Layout Lengkap Bawaan**: memulai dari tata letak standar lengkap beserta variabelnya.
2. Kamu juga dapat memilih gambar preset dari config `certificate-generator.backgrounds` saat membuat template baru, menduplikat template yang ada, atau menetapkan 1 template sebagai **Default kategori**.
3. Setiap model di aplikasi host (mis. `Event` atau `Course`) dapat memilih `certificate_design_id` spesifik dan meneruskannya ke parameter `'design'` saat `issue()`.

## Atur desain & gambar dasar

1. Buka tab **Desain** atau klik indikator palet di atas kanvas. Pilih palet (**Spinotek Azure & Cyan**, **Midnight Navy & Champagne Gold**, **Emerald & Ivory**) atau atur warna HEX (`#RRGGBB`) kustom.
2. Untuk mengunggah **Gambar Dasar Sertifikat** (JPG/PNG), batas per file adalah **5 MiB** dan **16 megapiksel** (dikirim lossless sebagai data URI tanpa kompresi ulang yang merusak ketajaman cetak). Halaman otomatis menyesuaikan ukuran proporsional gambar atau orientasi A4 Landscape/Portrait.
3. Matikan **Tampilkan bingkai** jika gambar dasar sudah memuat bingkai sendiri. Geser elemen di kanvas, tarik handle sudut/tepi, atau masukkan koordinat X/Y dalam milimeter.
4. Di tab **Elemen** maupun **Pengaturan**, tambahkan teks tetap, variabel bawaan, atau **Custom Variables** per template, serta elemen **Logo** / **Tanda tangan** (PNG transparan atau JPG).
5. Desain wajib memuat tepat satu QR verifikasi persegi dengan sisi minimal **30 mm**.
6. Klik **Pratinjau PDF** untuk menguji hasil dengan data contoh, lalu klik **Simpan desain** untuk menyimpan ke database.

Perubahan template hanya berlaku pada penerbitan baru. Setiap sertifikat terbit terhubung ke **design snapshot** (`spinotek_certificate_design_snapshots`) yang terdeduplikasi sehingga sertifikat lama tetap dapat diunduh ulang sesuai desain saat diterbitkan.

## Siapkan template awal dari file

Untuk menyediakan template awal saat memasang package di aplikasi, jalankan:

```bash
php artisan vendor:publish --tag=certificate-template
```

Edit `resources/certificates/certificate.json`. Gunakan [template bawaan](../resources/templates/certificate.json) sebagai acuan. Format versi 1 memakai satuan `mm`, mendukung kunci opsional `theme` (`spinotek-blue`, `midnight-navy`, `emerald-ivory`, atau `custom`) dan `custom_variables`.
