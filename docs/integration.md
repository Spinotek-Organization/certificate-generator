# Integrasi Laravel

[Instalasi](installation.md) · [Editor desain](editor.md) · [Pemecahan masalah](troubleshooting.md)

Package menerbitkan PDF dari data yang diberikan aplikasi. Aplikasi memilih peserta, memeriksa kelayakan, dan menentukan siapa yang boleh mengunduh. Contoh ini memakai tabel pendaftaran yang sudah ada.

## 1. Siapkan data

Contoh memakai model `Enrollment` dengan relasi `participant` untuk pengguna peserta, relasi `activity` untuk kegiatan, kolom `completed_at` untuk penanda selesai, dan policy `view` untuk memeriksa hak akses. Di aplikasi event atau kursus, ganti model dan aturan sesuai alur yang sudah dipakai.

Tambahkan kolom UUID nullable bernama `certificate_code` di tabel pendaftaran milik aplikasi. Di dalam blok `Schema::table('enrollments', ...)` pada migrasi, tambahkan:

```php
$table->uuid('certificate_code')->nullable()->unique();
```

Jalankan `php artisan migrate`. Kolom ini menyimpan kode dari Spinotek. Untuk contoh yang menggunakan satu record pendaftaran per peserta dan kegiatan, pengguna dapat mengunduh ulang PDF yang sama dari record tersebut.

## 2. Tambahkan rute dan controller

Tambahkan import yang diperlukan di bagian atas `routes/web.php`, lalu daftarkan rute. Gunakan POST karena klik pertama dapat membuat terbitan baru.

```php
use App\Http\Controllers\DownloadCertificateController;
use Illuminate\Support\Facades\Route;

Route::post('/enrollments/{enrollment}/certificate', DownloadCertificateController::class)
    ->middleware('auth')
    ->name('enrollments.certificate');
```

Buat file `app/Http/Controllers/DownloadCertificateController.php`:

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
                'issuer' => 'Spinotek Demo',
            ]);

            $enrollment->certificate_code = $certificate->code;
            $enrollment->save();

            return $certificate;
        }, attempts: 3);

        return $certificate->download();
    }
}
```

Pastikan policy `view` mengizinkan pemilik pendaftaran atau role yang kamu tentukan. Sebagai contoh, jika `participant_id` langsung menunjuk ke `users.id`, method pada `EnrollmentPolicy` dapat memeriksa:

```php
public function view(User $user, Enrollment $enrollment): bool
{
    return (string) $user->getKey() === (string) $enrollment->participant_id;
}
```

Laravel dapat menemukan `App\Policies\EnrollmentPolicy` berdasarkan penamaan bawaan. Jika aplikasi punya policy `Enrollment` sendiri, gunakan aturan yang sudah ada atau buat izin unduh khusus. Rute verifikasi tidak digunakan sebagai rute unduh.

Transaksi menggabungkan penerbitan dengan penyimpanan kode pada pendaftaran. Contoh mengasumsikan kedua tabel memakai koneksi database yang sama. Penguncian baris pada database yang mendukungnya membantu mencegah penerbitan ganda saat peserta menekan tombol hampir bersamaan.

## 3. Tampilkan tombol unduh

Di halaman pendaftaran, tampilkan tombol bagi peserta yang selesai dan punya akses. Ganti label dan kelas CSS sesuai tampilan aplikasi.

```blade
@if ($enrollment->completed_at)
    @can('view', $enrollment)
        <form method="POST" action="{{ route('enrollments.certificate', $enrollment) }}">
            @csrf
            <button type="submit">Unduh Sertifikat</button>
        </form>
    @endcan
@endif
```

Uji dengan akun peserta yang memenuhi syarat. Klik pertama membuat PDF. Klik berikutnya memakai kode yang sudah disimpan. Coba juga dengan peserta yang belum selesai dan akun lain untuk memastikan aksesnya ditolak.

## Referensi data penerbitan

`app(CertificateGenerator::class)->issue([...])` mengembalikan model `Spinotek\CertificateGenerator\Certificate`. Ambil `$certificate->code` untuk menyimpan hubungan ke peserta. Panggil `$certificate->download()` untuk mengirim respons PDF.

| Kunci | Keterangan |
| --- | --- |
| `participant_name` | Nama peserta, wajib, maksimal 255 karakter |
| `activity_title` | Nama kegiatan, wajib, maksimal 255 karakter |
| `issued_date` | Tanggal penerbitan, wajib, format `YYYY-MM-DD` |
| `issuer` | Nama penerbit, maksimal 255 karakter. Gunakan nilai dari pengaturan aplikasi atau biarkan memakai `APP_NAME`. |

Untuk terbitan baru, `$generator->verificationUrl($certificate->code)` menghasilkan URL dari konfigurasi yang aktif. Jika domain atau prefix berubah, PDF yang sudah diterbitkan tetap menyimpan URL pada saat terbit.

## Verifikasi dan penyimpanan

Halaman verifikasi bawaan ada di `/certificates/verify/{code}`. Saat kode ditemukan, halaman menunjukkan nama peserta, nama kegiatan, penerbit, tanggal penerbitan, dan status **Valid**. Kode yang tidak dikenal berstatus HTTP 404. Isi halaman ini dapat dibaca siapa pun yang mempunyai tautan.

Package menyimpan metadata dan PDF di `spinotek_certificates`. Desain yang dipakai untuk penerbitan disimpan di `spinotek_certificate_designs`. Ikutkan kedua tabel dan kolom kode di tabel peserta dalam pencadangan database aplikasi. Gunakan [konfigurasi prefix](troubleshooting.md#sesuaikan-url-editor-dan-verifikasi) jika rute bawaan bentrok dengan rute aplikasi.
