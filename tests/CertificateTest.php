<?php

use Composer\InstalledVersions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spinotek\CertificateGenerator\Certificate;
use Spinotek\CertificateGenerator\CertificateGenerator;
use Spinotek\CertificateGenerator\CertificateServiceProvider;
use Symfony\Component\Process\Process;
use Zxing\QrReader;

final class CertificateTest extends TestCase
{
    private const DATA = [
        'participant_name' => 'Rani & Putri',
        'activity_title' => 'Lokakarya Desain Informasi',
        'issued_date' => '2026-11-18',
    ];

    private string $directory;
    private string $template;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        $this->artisan('migrate')->assertSuccessful();

        $this->directory = sys_get_temp_dir().'/spinotek-'.Str::uuid();
        File::makeDirectory($this->directory);
        $this->template = $this->directory.'/certificate.json';
        File::copy(__DIR__.'/../resources/templates/certificate.json', $this->template);
        config(['certificate-generator.template' => $this->template]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_sandbox_installs_local_package_and_discovers_provider_and_migration(): void
    {
        $this->assertSame(realpath(__DIR__.'/..'), realpath(InstalledVersions::getInstallPath('spinotek/certificate-generator')));
        $this->assertInstanceOf(CertificateServiceProvider::class, app()->getProvider(CertificateServiceProvider::class));
        $this->assertTrue(Schema::hasTable('spinotek_certificates'));
        $this->assertTrue(app('router')->has('spinotek.certificates.verify'));
    }

    public function test_demo_creates_a_downloadable_pdf_and_prints_its_qr_url(): void
    {
        app()->useStoragePath($this->directory);
        config(['app.url' => 'https://penerbit.test/lms']);

        $this->artisan('certificate-generator:demo')
            ->expectsOutputToContain($this->directory.'/app/certificates/')
            ->expectsOutputToContain('https://penerbit.test/lms/certificates/verify/')
            ->assertSuccessful();

        $certificate = Certificate::query()->sole();
        $pdf = $this->directory.'/app/certificates/'.$certificate->code.'.pdf';
        $this->assertSame('Rani Putri', $certificate->participant_name);
        $this->assertSame($certificate->download()->getContent(), File::get($pdf));

        $png = (new Process(['pdftoppm', '-r', '110', '-png', '-singlefile', $pdf]))->mustRun()->getOutput();
        $url = (new QrReader($png, QrReader::SOURCE_TYPE_BLOB, false))->text(['TRY_HARDER' => true]);
        $this->assertSame('https://penerbit.test/lms/certificates/verify/'.$certificate->code, $url);
    }

    public function test_install_runs_only_package_migrations_and_is_safe_to_retry(): void
    {
        DB::purge('sqlite');
        app()->useDatabasePath($this->directory);
        File::ensureDirectoryExists($this->directory.'/migrations');
        File::put($this->directory.'/migrations/2026_01_01_000000_host_migration.php',
            '<?php throw new RuntimeException("Migrasi aplikasi tidak boleh dijalankan oleh installer Spinotek.");');

        $this->artisan('certificate-generator:install')
            ->expectsOutputToContain('Tabel Spinotek Certificate Generator siap.')
            ->assertSuccessful();
        $this->assertTrue(Schema::hasTable('spinotek_certificates'));
        $this->assertTrue(Schema::hasTable('spinotek_certificate_designs'));
        $this->artisan('certificate-generator:install')->assertSuccessful();
        $this->assertDatabaseCount('migrations', 2);
    }

    public function test_demo_does_not_issue_a_certificate_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $this->artisan('certificate-generator:demo')
            ->expectsOutputToContain('Demo hanya tersedia')
            ->assertFailed();
        $this->assertDatabaseCount('spinotek_certificates', 0);
    }

    public function test_demo_explains_missing_migrations(): void
    {
        Schema::drop('spinotek_certificate_designs');

        $this->artisan('certificate-generator:demo')
            ->expectsOutputToContain('php artisan migrate')
            ->assertFailed();
        $this->assertDatabaseCount('spinotek_certificates', 0);
    }

    public function test_issued_pdf_contains_participant_details_and_a_scannable_verification_url(): void
    {
        $this->get('http://alias.test/'); // Issuance must use the configured issuer domain.
        $certificate = app(CertificateGenerator::class)->issue(self::DATA);
        $download = $certificate->download();
        $this->assertSame('application/pdf', $download->headers->get('Content-Type'));
        $this->assertStringContainsString($certificate->code.'.pdf', $download->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $download->getContent());

        $pdf = $this->directory.'/issued.pdf';
        File::put($pdf, $download->getContent());
        $text = (new Process(['pdftotext', $pdf, '-']))->mustRun()->getOutput();
        foreach ([...array_values(self::DATA), 'Akademi Contoh', 'Valid'] as $field) {
            $this->assertStringContainsString($field, $text);
        }

        $prefix = $this->directory.'/page';
        (new Process(['pdftoppm', '-r', '110', '-png', $pdf, $prefix]))->mustRun();
        $this->assertCount(1, glob($prefix.'-*.png'));
        $url = (new QrReader($prefix.'-1.png', useImagickIfAvailable: false))->text(['TRY_HARDER' => true]);
        $this->assertSame('https://penerbit.test/certificates/verify/'.$certificate->code, $url);

        config(['app.name' => 'Nama penerbit setelah perubahan']);
        $this->get($url)->assertOk()->assertSeeText('Rani & Putri')
            ->assertSeeText(self::DATA['activity_title'])->assertSeeText('Akademi Contoh')
            ->assertSeeText(self::DATA['issued_date'])->assertSeeText('Valid');
    }

    public function test_unknown_code_returns_not_found(): void
    {
        $this->get('/certificates/verify/'.Str::uuid())->assertNotFound()->assertSeeText('Sertifikat tidak ditemukan');
    }

    public function test_download_preserves_original_bytes_after_a_template_edit(): void
    {
        $generator = app(CertificateGenerator::class);
        $first = $generator->issue(self::DATA);
        $original = $first->download()->getContent();

        $layout = File::json($this->template);
        $layout['elements'][0]['text'] = 'Sertifikat Edisi Kedua';
        File::put($this->template, json_encode($layout, JSON_THROW_ON_ERROR));
        $second = $generator->issue(self::DATA);
        $this->assertNotSame($first->code, $second->code);
        $newText = (new Process(['pdftotext', '-', '-']))->setInput($second->download()->getContent())->mustRun()->getOutput();
        $this->assertStringContainsString('Edisi Kedua', $newText);
        $this->assertSame($original, Certificate::findOrFail($first->code)->download()->getContent());
    }

    public function test_long_unicode_name_fits_inside_a_small_template_box(): void
    {
        $layout = File::json($this->template);
        $name = 'Élodie Alexandrina Müller Fernández';
        $text = [
            'id' => 'recipient', 'type' => 'variable', 'field' => 'participant_name',
            'x' => 20, 'y' => 35, 'width' => 42, 'height' => 10, 'font_size' => 32,
        ];
        $qr = collect($layout['elements'])->firstWhere('type', 'qr');
        $layout['elements'] = [$text, $qr];
        File::put($this->template, json_encode($layout, JSON_THROW_ON_ERROR));
        $certificate = app(CertificateGenerator::class)->issue(array_replace(self::DATA, ['participant_name' => $name]));
        $bbox = (new Process(['pdftotext', '-bbox', '-', '-']))
            ->setInput($certificate->download()->getContent())->mustRun()->getOutput();
        $words = simplexml_load_string($bbox)->xpath('//*[local-name()="word"]');
        $this->assertSame(explode(' ', $name), array_map('strval', $words));
        foreach ($words as $word) {
            $this->assertGreaterThanOrEqual(20 * 72 / 25.4 - 1, (float) $word['xMin']);
            $this->assertLessThanOrEqual(62 * 72 / 25.4 + 1, (float) $word['xMax']);
            $this->assertGreaterThanOrEqual(35 * 72 / 25.4 - 1, (float) $word['yMin']);
            $this->assertLessThanOrEqual(45 * 72 / 25.4 + 1, (float) $word['yMax']);
        }
    }

    public function test_invalid_date_does_not_issue_a_certificate(): void
    {
        $this->expectException(ValidationException::class);
        try {
            app(CertificateGenerator::class)->issue(array_replace(self::DATA, ['issued_date' => '2026-02-30']));
        } finally {
            $this->assertDatabaseCount('spinotek_certificates', 0);
        }
    }

    public function test_template_without_a_qr_cannot_issue_a_certificate(): void
    {
        $layout = File::json($this->template);
        $layout['elements'] = array_filter($layout['elements'], fn ($element) => $element['type'] !== 'qr');
        File::put($this->template, json_encode($layout, JSON_THROW_ON_ERROR));

        $this->expectException(UnexpectedValueException::class);
        try {
            app(CertificateGenerator::class)->issue(self::DATA);
        } finally {
            $this->assertDatabaseCount('spinotek_certificates', 0);
        }
    }
}
