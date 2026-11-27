<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Spinotek\CertificateGenerator\CertificateGenerator;
use Symfony\Component\Process\Process;
use Zxing\QrReader;

final class CustomPrefixTest extends TestCase
{
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../sandbox/bootstrap/app.php';
        $app->afterBootstrapping(LoadConfiguration::class, function (Application $app) {
            $app['config']->set('certificate-generator.editor_prefix', '/admin/piagam/');
            $app['config']->set('certificate-generator.download_prefix', '/piagam/unduh/');
            $app['config']->set('certificate-generator.verification_prefix', '/piagam/cek/');
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
            $app['config']->set('database.connections.sqlite.url', null);
        });
        $app->booting(function () {
            Route::get('/certificates/verify/{verificationCode}', fn (string $verificationCode) => 'Arsip aplikasi '.$verificationCode)
                ->name('certificates.verify');
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_custom_routes_and_printed_qr_coexist_with_host_verification(): void
    {
        $this->artisan('migrate')->assertSuccessful();

        $this->assertSame('/admin/piagam', route('spinotek.design.index', absolute: false));
        $this->assertSame('/admin/piagam/editor', route('spinotek.design.edit', absolute: false));
        $this->getJson('/admin/piagam')->assertUnauthorized();
        Gate::define('manage-certificate-design', fn () => false);
        $this->actingAs(new GenericUser(['id' => 2]))->get('/admin/piagam')->assertForbidden();
        $this->actingAs(new GenericUser(['id' => 2]))->get('/admin/piagam/editor')->assertForbidden();
        $this->get('/certificates/design')->assertNotFound();

        $generator = app(CertificateGenerator::class);
        $certificate = $generator->issue([
            'participant_name' => 'Rani Putri',
            'activity_title' => 'Lokakarya',
            'issued_date' => '2026-11-18',
        ]);

        $this->assertSame('/piagam/unduh/'.$certificate->code, route('spinotek.certificates.download', ['code' => $certificate->code], false));
        $expectedVerifyUrl = 'https://penerbit.test/piagam/cek/'.$certificate->code;
        $this->assertSame($expectedVerifyUrl, $generator->verificationUrl($certificate->code));

        $this->get($expectedVerifyUrl)->assertOk()->assertSeeText('Rani Putri');
        $this->get(route('certificates.verify', $certificate->code))->assertOk()->assertSeeText('Arsip aplikasi '.$certificate->code);

        if (trim((string) @shell_exec('command -v pdftoppm')) !== '') {
            $page = (new Process(['pdftoppm', '-r', '110', '-png', '-singlefile', '-']))
                ->setInput($certificate->download()->getContent())->mustRun()->getOutput();
            $url = (new QrReader($page, QrReader::SOURCE_TYPE_BLOB, false))->text(['TRY_HARDER' => true]);
            $this->assertSame($expectedVerifyUrl, $url);
        }
    }
}
