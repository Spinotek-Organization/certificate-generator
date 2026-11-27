<?php

return [
    'editor_prefix' => env('CERTIFICATE_EDITOR_PREFIX', 'certificates/design'),
    'download_prefix' => env('CERTIFICATE_DOWNLOAD_PREFIX', 'certificates/download'),
    'verification_prefix' => env('CERTIFICATE_VERIFICATION_PREFIX', 'certificates/verify'),

    'editor_middleware' => ['web', 'auth', 'can:manage-certificate-design'],
    'download_middleware' => ['web'],

    // 'on_demand' (paling hemat resource: simpan snapshot desain deduplikasi + variabel JSON, render PDF saat diunduh)
    // 'disk' (simpan file PDF ke Storage disk)
    'storage' => [
        'driver' => env('CERTIFICATE_STORAGE_DRIVER', 'on_demand'),
        'disk' => env('CERTIFICATE_STORAGE_DISK', 'local'),
        'path' => 'certificates',
    ],

    // Format nomor sertifikat default (bisa dioverride per template di panel Creator atau saat issue())
    // Token: {PREFIX}, {CATEGORY}, {CAT}, {YYYY}, {YY}, {MM}, {DD}, {SEQ}, {SEQ:4}, {SEQ:5}, {RAND:4}, {UUID_SHORT}
    'number' => [
        'format' => env('CERTIFICATE_NUMBER_FORMAT', 'CERT/{CAT}/{YYYY}/{SEQ:5}'),
        'prefix' => env('CERTIFICATE_NUMBER_PREFIX', 'CERT'),
        'category_codes' => [
            'course' => 'CRS',
            'event' => 'EVT',
            'ebook' => 'EBK',
            'general' => 'GEN',
        ],
    ],

    // Kategori yang tampil di selector panel Certificate Creator & halaman verifikasi
    'categories' => [
        'general' => 'Umum',
        'event' => 'Event',
        'course' => 'E-Course',
    ],

    // Variabel bawaan; aplikasi host dapat menambah custom variables di sini atau via CertificateGenerator::registerVariables()
    'variables' => [
        'participant_name' => 'Nama Peserta',
        'activity_title' => 'Judul Kegiatan / Kelas',
        'certificate_number' => 'Nomor Sertifikat',
        'category' => 'Kategori',
        'issuer' => 'Penerbit',
        'issued_date' => 'Tanggal Terbit',
        'status' => 'Status',
    ],

    // Variabel tambahan (di luar field utama) yang boleh ditampilkan pada halaman verifikasi publik
    'public_variables' => [],

    // Gambar dasar preset yang tersedia di panel Certificate Creator
    // Format: ['key' => ['label' => 'Nama Preset', 'path' => public_path('...')]]
    'backgrounds' => [],

    // Batas upload gambar dasar & aset desain (tanpa kompresi lossy)
    'upload' => [
        'max_kb' => 5120,
        'max_bytes' => 5 * 1024 * 1024,
        'max_megapixels' => 16_000_000,
    ],

    // URL kembali dari editor ke panel admin aplikasi host (opsional)
    'admin_back_url' => env('CERTIFICATE_ADMIN_BACK_URL', '/'),
];
