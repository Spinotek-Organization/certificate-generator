# Editor desain

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

Atur Gate sekali per aplikasi. Aplikasi yang menerbitkan lewat kode dengan desain bawaan dapat melewati konfigurasi editor. Route verifikasi QR bersifat publik.

## Buka editor dari panel admin

Masuk sebagai admin, lalu buka `/certificates/design` pada domain aplikasi. Untuk menampilkan menu di sidebar Blade, sisipkan:

```blade
@can('manage-certificate-design')
    <a href="{{ route('spinotek.design.edit') }}">Desain Sertifikat</a>
@endcan
```

Tambahkan tautan tersebut di layout milik aplikasi agar mengikuti struktur menu yang sudah ada. Nama rute tetap sama jika prefix URL diubah.

## Atur dan uji desain

1. Mulai dengan template bawaan (**Midnight Navy & Champagne Gold**), pilih palet kedua (**Emerald & Ivory**), atau atur **Warna latar**, **Warna aksen**, dan **Warna bingkai** secara manual memakai kode HEX (`#RRGGBB`). Anda juga tetap dapat mengunggah latar JPG/PNG hingga **1 MiB** dan **9 megapiksel**. Gambar memenuhi ukuran halaman. Untuk menghindari distorsi, siapkan latar dengan rasio yang sama dengan halaman.
2. Matikan **Tampilkan bingkai** jika latar sudah memuat bingkai. Pilih elemen di kanvas atau dari daftar, lalu geser atau masukkan nilai X/Y dalam milimeter. Saat elemen fokus di kanvas, tombol panah menggeser 1 mm. Tahan Shift untuk menggeser 5 mm.
3. Tambahkan teks tetap, seperti judul sertifikat, atau pilih variabel untuk nama peserta, kegiatan, penerbit, tanggal, dan status. Atur font, warna, dan perataan. Elemen harus berada dalam batas halaman.
4. Di **Elemen baru**, pilih **Logo** atau **Tanda tangan**, klik **Tambah**, lalu pilih JPG atau PNG. Ubah posisi dan ukuran kotaknya. Gambar tetap proporsional dan ditempatkan di tengah kotak. Gunakan PNG transparan jika latar sertifikat perlu terlihat di belakang gambar. Batas total data gambar per desain sekitar **3 MiB**, termasuk latar.
5. Pindahkan atau ubah ukuran QR. Desain wajib memuat tepat satu QR persegi dengan sisi minimal 30 mm. QR selalu dicetak gelap di atas bidang putih agar mudah dipindai.
6. Klik **Preview PDF** untuk memeriksa draf. Preview memakai data contoh dan QR yang mengarah ke editor. Klik **Simpan desain** untuk memakai desain pada penerbitan berikutnya.

Satu instalasi menyimpan satu desain aktif. Perubahan desain berlaku pada terbitan baru. PDF yang sudah diterbitkan disimpan agar bisa diunduh kembali dengan isi yang sama.

## Siapkan template awal dari file

Untuk menyediakan template awal saat memasang package di aplikasi, jalankan:

```bash
php artisan vendor:publish --tag=certificate-template
```

Edit `resources/certificates/certificate.json`. Gunakan [template bawaan](../resources/templates/certificate.json) sebagai acuan. Jika admin sudah menyimpan desain di editor, aplikasi akan memakai desain tersimpan terlebih dahulu.

Format versi 1 menggunakan satuan `mm`. Koordinat diukur dari sudut kiri atas. Halaman mendefinisikan `width` dan `height`. Opsi `theme` bersifat opsional dengan kunci `palette` (`midnight-navy`, `emerald-ivory`, atau `custom`), `background`, `accent`, dan `frame` dalam format HEX `#RRGGBB`. Desain lama tanpa `theme` masih bisa dibuka di editor.

Setiap elemen punya `id` yang unik, `type` (`text`, `variable`, `image`, atau `qr`), `x`, `y`, `width` dan `height`. Elemen teks memakai ukuran huruf dalam poin, font `sans`, `serif`, atau `mono`, warna `#RRGGBB`, dan perataan `left`, `center`, atau `right`. Elemen `image` menyimpan JPG atau PNG sebagai data URI pada kunci `image`, dengan nama opsional pada kunci `label`.
