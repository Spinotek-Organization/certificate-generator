<?php

use Composer\InstalledVersions;
use Illuminate\Auth\GenericUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spinotek\CertificateGenerator\Certificate;
use Spinotek\CertificateGenerator\CertificateDesign;
use Spinotek\CertificateGenerator\CertificateGenerator;
use Spinotek\CertificateGenerator\CertificateServiceProvider;
use Spinotek\CertificateGenerator\Models\CertificateDesignRecord;
use Spinotek\CertificateGenerator\Support\VariableRegistry;
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
        VariableRegistry::clearRuntime();
    }

    protected function tearDown(): void
    {
        VariableRegistry::clearRuntime();
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_sandbox_installs_local_package_and_discovers_provider_and_migration(): void
    {
        $this->assertSame(realpath(__DIR__.'/..'), realpath(InstalledVersions::getInstallPath('spinotek/certificate-generator')));
        $this->assertTrue($this->app->providerIsLoaded(CertificateServiceProvider::class));
        $this->assertTrue(Schema::hasTable('spinotek_certificate_designs'));
        $this->assertTrue(Schema::hasTable('spinotek_certificate_design_snapshots'));
        $this->assertTrue(Schema::hasTable('spinotek_certificates'));
    }

    public function test_demo_creates_a_downloadable_pdf_and_prints_its_qr_url(): void
    {
        $path = null;
        try {
            $exitCode = Artisan::call('certificate-generator:demo');
            $this->assertSame(0, $exitCode);
            $output = Artisan::output();

            preg_match('/^PDF:\s+(.+)$/m', $output, $pdfMatch);
            preg_match('/^Nomor:\s+(.+)$/m', $output, $numberMatch);
            preg_match('/^Verifikasi:\s+(https?:\/\/.+)$/m', $output, $urlMatch);

            $this->assertNotEmpty($pdfMatch[1] ?? null);
            $this->assertNotEmpty($numberMatch[1] ?? null);
            $this->assertNotEmpty($urlMatch[1] ?? null);

            $path = trim($pdfMatch[1]);
            $this->assertFileExists($path);
            $pdf = File::get($path);
            $this->assertStringStartsWith('%PDF-1.', $pdf);

            $this->get(trim($urlMatch[1]))
                ->assertOk()
                ->assertSeeText('Rani Putri')
                ->assertSeeText('Lokakarya Desain Informasi')
                ->assertSeeText(trim($numberMatch[1]))
                ->assertSeeText(Certificate::STATUS_VALID);
        } finally {
            if ($path !== null) {
                File::delete($path);
            }
        }
    }

    public function test_install_runs_only_package_migrations_and_is_safe_to_retry(): void
    {
        DB::connection()->getSchemaBuilder()->dropAllTables();
        DB::connection()->getSchemaBuilder()->create('migrations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });

        $this->artisan('certificate-generator:install')->assertSuccessful();
        $this->artisan('certificate-generator:install')->assertSuccessful();

        $this->assertTrue(Schema::hasTable('spinotek_certificate_designs'));
        $this->assertTrue(Schema::hasTable('spinotek_certificate_design_snapshots'));
        $this->assertTrue(Schema::hasTable('spinotek_certificates'));
        $this->assertSame(3, DB::table('migrations')->count());
    }

    public function test_demo_does_not_issue_a_certificate_in_production(): void
    {
        $this->app['env'] = 'production';
        try {
            $this->artisan('certificate-generator:demo')->assertFailed();
            $this->assertSame(0, Certificate::query()->count());
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    public function test_demo_explains_missing_migrations(): void
    {
        Schema::drop('spinotek_certificates');

        $this->artisan('certificate-generator:demo')
            ->expectsOutputToContain('certificate-generator:install')
            ->assertFailed();
    }

    public function test_issued_pdf_contains_participant_details_and_a_scannable_verification_url(): void
    {
        $this->get('http://alias.test/'); // Issuance must use configured APP_URL domain.
        $certificate = app(CertificateGenerator::class)->issue(self::DATA);

        $this->assertNotEmpty($certificate->certificate_number);
        $this->assertSame('general', $certificate->category);
        $pdf = $certificate->renderPdf();
        $this->assertStringStartsWith('%PDF-1.', $pdf);

        $expectedUrl = 'https://penerbit.test/certificates/verify/'.$certificate->code;
        $this->get($expectedUrl)
            ->assertOk()
            ->assertSeeText('Rani & Putri')
            ->assertSeeText('Lokakarya Desain Informasi')
            ->assertSeeText($certificate->certificate_number)
            ->assertSeeText('Akademi Contoh')
            ->assertSeeText('18 Nov 2026')
            ->assertSeeText(Certificate::STATUS_VALID);

        if (! $this->hasPoppler()) {
            return;
        }

        $text = (new Process(['pdftotext', '-', '-']))->setInput($pdf)->mustRun()->getOutput();
        $this->assertStringContainsString('Rani & Putri', $text);
        $this->assertStringContainsString('Lokakarya Desain Informasi', $text);

        $page = (new Process(['pdftoppm', '-r', '110', '-png', '-singlefile', '-']))
            ->setInput($pdf)->mustRun()->getOutput();
        $qrUrl = (new QrReader($page, QrReader::SOURCE_TYPE_BLOB, false))->text(['TRY_HARDER' => true]);
        $this->assertSame($expectedUrl, $qrUrl);
    }

    public function test_manual_verification_lookup_and_unknown_code_handling(): void
    {
        $certificate = app(CertificateGenerator::class)->issue([
            ...self::DATA,
            'category' => 'event',
        ]);

        $this->get('/certificates/verify')->assertOk();

        $this->get('/certificates/verify?q='.urlencode($certificate->certificate_number))
            ->assertOk()
            ->assertSeeText('Rani & Putri')
            ->assertSeeText($certificate->certificate_number);

        $this->get('/certificates/verify?q='.urlencode($certificate->code))
            ->assertOk()
            ->assertSeeText('Rani & Putri');

        $this->get('/certificates/verify?q=NOT-FOUND-999')
            ->assertOk()
            ->assertDontSeeText('Rani & Putri');

        $this->get('/certificates/verify/'.Str::uuid())->assertNotFound();
    }

    public function test_download_preserves_original_bytes_after_a_template_edit(): void
    {
        $generator = app(CertificateGenerator::class);
        $first = $generator->issue(self::DATA);
        $firstPdf = $first->renderPdf();

        $layout = CertificateDesign::current();
        $layout['elements'][0]['text'] = 'PIAGAM KELULUSAN EDISI BARU';
        CertificateDesign::save($layout);

        $second = $generator->issue(self::DATA);
        $this->assertNotSame($first->design_snapshot_id, $second->design_snapshot_id);
        $this->assertSame($this->normalizePdf($firstPdf), $this->normalizePdf($first->fresh(['snapshot'])->renderPdf()));
        $this->assertNotSame($this->normalizePdf($firstPdf), $this->normalizePdf($second->renderPdf()));
    }

    public function test_multi_category_templates_custom_variables_and_issue_or_get(): void
    {
        CertificateGenerator::registerVariables([
            'instructor' => 'Instruktur',
        ]);

        $eventLayout = CertificateDesign::defaultLayout();
        $eventLayout['elements'][0]['text'] = 'SERTIFIKAT EVENT KHUSUS';
        $eventDesign = CertificateDesign::createTemplate([
            'name' => 'Template Event',
            'category' => 'event',
            'is_default' => true,
            'number_format' => 'EVT/{YYYY}/{SEQ:4}',
            'layout' => $eventLayout,
        ]);

        $courseLayout = CertificateDesign::defaultLayout();
        $courseLayout['elements'][0]['text'] = 'SERTIFIKAT E-COURSE';
        $courseDesign = CertificateDesign::createTemplate([
            'name' => 'Template Course',
            'category' => 'course',
            'is_default' => true,
            'number_format' => 'CRS/{YYYY}/{SEQ:4}',
            'layout' => $courseLayout,
        ]);

        $generator = app(CertificateGenerator::class);

        $eventCert = $generator->issue([
            ...self::DATA,
            'category' => 'event',
            'variables' => ['instructor' => 'Budi Santoso'],
        ]);
        $this->assertSame($eventDesign->id, $eventCert->design_id);
        $this->assertStringStartsWith('EVT/2026/', $eventCert->certificate_number);
        $this->assertSame('Budi Santoso', $eventCert->templateFields()['instructor']);

        $courseCert1 = $generator->issueOrGet($courseDesign, 7, [
            ...self::DATA,
            'category' => 'course',
            'design' => $courseDesign->id,
        ]);
        $courseCert2 = $generator->issueOrGet($courseDesign, 7, [
            ...self::DATA,
            'category' => 'course',
            'design' => $courseDesign->id,
        ]);
        $this->assertSame($courseCert1->code, $courseCert2->code);
        $this->assertStringStartsWith('CRS/2026/', $courseCert1->certificate_number);
    }

    public function test_download_route_gate_signed_url_and_revoke_restore(): void
    {
        $generator = app(CertificateGenerator::class);
        $certificate = $generator->issue([
            ...self::DATA,
            'user_id' => 10,
        ]);

        $unsignedUrl = $generator->downloadUrl($certificate->code);
        $signedUrl = $generator->downloadUrl($certificate->code, signed: true);

        $this->get($unsignedUrl)->assertForbidden();

        $this->actingAs(new GenericUser(['id' => 99]));
        $this->get($unsignedUrl)->assertForbidden();

        $this->actingAs(new GenericUser(['id' => 10]));
        $this->get($unsignedUrl)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        // Signed link works even for guests while certificate is valid.
        $this->app['auth']->forgetGuards();
        $this->get($signedUrl)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        Gate::define('manage-certificate-design', fn ($user) => (int) $user->getAuthIdentifier() === 1);

        $revoked = $generator->revoke($certificate, 'Uji pencabutan');
        $this->assertTrue($revoked->isRevoked());
        $this->get($signedUrl)->assertForbidden();
        $this->get('/certificates/verify/'.$revoked->code)
            ->assertOk()
            ->assertSeeText(Certificate::STATUS_REVOKED);

        $restored = $generator->restore($revoked);
        $this->assertTrue($restored->isValid());
        $this->get($signedUrl)->assertOk();
    }

    public function test_long_unicode_name_fits_inside_a_small_template_box(): void
    {
        $layout = CertificateDesign::defaultLayout();
        foreach ($layout['elements'] as &$element) {
            if (($element['field'] ?? null) === 'participant_name') {
                $element['x'] = 18;
                $element['y'] = 78;
                $element['width'] = 80;
                $element['height'] = 10;
                $element['font_size'] = 24;
            }
        }
        unset($element);
        CertificateDesign::save($layout);

        $name = 'Dr. Raden Mas Suryakusuma Adipati Wijaya Kusumaningrat Sarjana Pendidikan';
        $certificate = app(CertificateGenerator::class)->issue(['participant_name' => $name] + self::DATA);
        $pdf = $certificate->renderPdf();
        $this->assertStringStartsWith('%PDF-1.', $pdf);

        if (! $this->hasPoppler()) {
            return;
        }

        $text = (new Process(['pdftotext', '-', '-']))->setInput($pdf)->mustRun()->getOutput();
        $this->assertStringContainsString('Kusumaningrat', $text);
    }

    public function test_invalid_date_does_not_issue_a_certificate(): void
    {
        try {
            app(CertificateGenerator::class)->issue(['issued_date' => 'bukan-tanggal'] + self::DATA);
            $this->fail('ValidationException harus dilempar untuk tanggal tidak valid.');
        } catch (ValidationException) {
            $this->assertSame(0, Certificate::query()->count());
        }
    }

    public function test_template_without_a_qr_cannot_issue_a_certificate(): void
    {
        $layout = CertificateDesign::defaultLayout();
        $layout['elements'] = array_values(array_filter(
            $layout['elements'],
            static fn (array $el): bool => $el['type'] !== 'qr'
        ));
        CertificateDesignRecord::query()->create([
            'name' => 'Tanpa QR',
            'slug' => 'tanpa-qr',
            'is_default' => true,
            'layout' => $layout,
        ]);

        try {
            app(CertificateGenerator::class)->issue(self::DATA);
            $this->fail('UnexpectedValueException harus dilempar bila QR tidak ada.');
        } catch (UnexpectedValueException) {
            $this->assertSame(0, Certificate::query()->count());
        }
    }

    private function normalizePdf(string $pdf): string
    {
        return preg_replace(
            ['/\/ID\s*\[[^\]]+\]/', '/\/(CreationDate|ModDate)\s*\([^)]+\)/'],
            ['/ID[<norm><norm>]', '/$1(D:norm)'],
            $pdf
        ) ?? $pdf;
    }

    private function hasPoppler(): bool
    {
        return trim((string) @shell_exec('command -v pdftoppm')) !== ''
            && trim((string) @shell_exec('command -v pdftotext')) !== '';
    }
}
