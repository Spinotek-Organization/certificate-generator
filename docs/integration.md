# Integrasi Laravel

[Instalasi](installation.md) · [Editor desain](editor.md) · [Pemecahan masalah](troubleshooting.md)

Package menerbitkan dan mengelola sertifikat PDF dari aplikasi Laravel secara `on_demand` (hemat storage via snapshot desain terdeduplikasi + variabel JSON) maupun `disk` PDF cache.

## 1. Integrasi pada Model / Tabel Aplikasi

Untuk menerbitkan sertifikat sekali per peserta & kegiatan secara idempoten, package menyediakan `$generator->issueOrGet($certifiable, $userId, $data)` yang mencari sertifikat tersimpan berdasarkan relasi polimorfik `(certifiable_type, certifiable_id, user_id)` pada `spinotek_certificates`.

Jika ingin memilih template per item (misalnya Event A dan Course B memiliki template berbeda), tambahkan kolom `certificate_design_id` nullable pada model Event/Course dan teruskan ke parameter `'design'`:

```php
$certificate = $generator->issueOrGet($event, $user->id, [
    'category' => 'event',
    'design' => $event->certificate_design_id,
    'participant_name' => $user->name,
    'activity_title' => $event->title,
    'issued_date' => today()->toDateString(),
    'issuer' => config('app.name'),
    'variables' => [
        'instructor' => $event->speaker_name,
    ],
]);
```

Alternatif lain, simpan `certificate_code` (UUID nullable) pada tabel pendaftaran aplikasi jika alur aplikasi sudah memilikinya.

## 2. Rute Unduh Bawaan & Gate Akses

Package menyediakan rute unduh bawaan (`spinotek.certificates.download`):

- `$generator->downloadUrl($certificate->code)` — memerlukan user login dan otorisasi Gate `download-certificate`. Secara default, `CertificateServiceProvider` mengizinkan pemilik (`$certificate->user_id`). Aplikasi host dapat mendefinisikan Gate `download-certificate` sendiri untuk mengizinkan admin atau instruktur.
- `$generator->downloadUrl($certificate->code, signed: true)` — membuat URL bertanda tangan untuk unduhan langsung selama sertifikat berstatus **Valid**.

```blade
<a href="{{ app(\Spinotek\CertificateGenerator\CertificateGenerator::class)->downloadUrl($certificate->code) }}">
    Unduh Sertifikat PDF
</a>
```

Untuk mencabut atau memulihkan sertifikat:

```php
$generator->revoke($certificate, 'Data peserta diperbarui');
$generator->restore($certificate);
```

Sertifikat berstatus **Dicabut** tidak dapat diunduh melalui rute publik/peserta.

## 3. Custom Variables & Nomor Sertifikat Kustom

Daftarkan variabel tambahan secara global lewat `config/certificate-generator.php` (`variables` & `public_variables`) atau pada `AppServiceProvider::boot()`:

```php
\Spinotek\CertificateGenerator\CertificateGenerator::registerVariables([
    'instructor' => 'Nama Instruktur',
    'duration_hours' => 'Durasi (Jam)',
]);
```

Atau tambahkan **Custom Variables** langsung dari panel **Pengaturan** di editor template. Nilai variabel dikirim melalui kunci `'variables'` saat memanggil `issue()` / `issueOrGet()`.

Untuk penomoran, token yang didukung pada `number_format` meliputi `{PREFIX}`, `{CATEGORY}`, `{CAT}`, `{YYYY}`, `{YY}`, `{MM}`, `{DD}`, `{SEQ}`, `{SEQ:4}`, `{SEQ:5}`, `{RAND:4}`, dan `{UUID_SHORT}`. Anda juga dapat mengirimkan `'certificate_number'` eksplisit atau mendaftarkan resolver kustom via `CertificateNumberGenerator::resolveUsing(...)`.

## 4. Referensi Data Penerbitan

`app(CertificateGenerator::class)->issue([...])` mengembalikan model `Spinotek\CertificateGenerator\Certificate`.

| Kunci | Keterangan |
| --- | --- |
| `participant_name` | Nama peserta, wajib, maksimal 255 karakter |
| `activity_title` | Judul kegiatan / kelas, wajib, maksimal 255 karakter |
| `issued_date` | Tanggal penerbitan, wajib, format `YYYY-MM-DD` (atau `DateTimeInterface`) |
| `issuer` | Nama penerbit, opsional (default `APP_NAME`) |
| `category` | Kategori program (`general`, `event`, `course`, dll.; default `general`) |
| `design` | Opsional: ID, slug, atau instance `CertificateDesignRecord` spesifik; jika kosong otomatis memakai default kategori |
| `user_id` | Opsional: ID user penerima untuk Gate unduh & lookup `issueOrGet` |
| `certifiable` | Opsional: Model Eloquent sumber kegiatan (disimpan secara polimorfik) |
| `certificate_number` | Opsional: nomor sertifikat eksplisit |
| `number_format` | Opsional: override format nomor sertifikat |
| `variables` | Opsional: array pasangan key => value untuk variabel kustom |

## 5. Verifikasi Publik & Penyimpanan Hemat Resource

- QR pada PDF mengarah ke `/certificates/verify/{code}` (HTTP 404 jika kode tidak ada).
- Halaman publik `/certificates/verify` menyediakan form pencarian manual berdasarkan **Nomor Sertifikat** (`certificate_number`) atau **Kode UUID** (`?q=...`).
- Secara default `storage.driver` bernilai `'on_demand'`: PDF dirender saat diunduh memakai `spinotek_certificate_design_snapshots` (deduplikasi SHA-256 layout) dan kolom JSON `variables` pada `spinotek_certificates` sehingga tidak membebani kapasitas database maupun storage.
