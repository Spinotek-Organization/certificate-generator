<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spinotek\CertificateGenerator\CertificateGenerator;
use Symfony\Component\Process\Process;
use Zxing\QrReader;

final class DesignEditorTest extends TestCase
{
    private const EDITOR = '/certificates/design';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        $this->artisan('migrate')->assertSuccessful();
        Gate::define('manage-certificate-design', fn (Authenticatable $user) => $user->getAuthIdentifier() === 1);
    }

    public function test_host_gate_blocks_guests_and_non_admins_on_all_editor_endpoints(): void
    {
        foreach ([null, new GenericUser(['id' => 2])] as $user) {
            if ($user) {
                $this->actingAs($user);
            }
            $this->get(self::EDITOR)->assertForbidden();
            $this->postJson(self::EDITOR.'/background')->assertForbidden();
            $this->postJson(self::EDITOR.'/preview')->assertForbidden();
            $this->putJson(self::EDITOR)->assertForbidden();
        }
        $this->get('/sandbox/admin')->assertForbidden();
        $this->get('/certificates/verify/'.Str::uuid())->assertNotFound();
    }

    public function test_edited_design_survives_reload_and_matches_issuance_outside_the_unique_qr(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));
        $page = $this->get(self::EDITOR)->assertOk()->assertSee('Simpan desain');
        $layout = $page->viewData('layout');
        $data = $page->viewData('sample');
        $old = app(CertificateGenerator::class)->issue($data);
        $oldPdf = $old->download()->getContent();

        $bytes = $this->backgroundPng();
        $upload = $this->post(self::EDITOR.'/background', ['background' => UploadedFile::fake()->createWithContent('paper.png', $bytes)])
            ->assertOk()->json('background');
        $layout['background'] = $upload;
        $layout['frame'] = false;
        foreach ($layout['elements'] as &$element) {
            if ($element['id'] === 'recipient') {
                $element = array_replace($element, [
                    'x' => 50, 'y' => 53, 'width' => 160, 'height' => 18, 'font_size' => 20,
                    'font' => 'mono', 'color' => '#a5263c', 'align' => 'right',
                ]);
            }
            if ($element['type'] === 'qr') {
                $element = array_replace($element, ['x' => 3, 'y' => 177, 'width' => 30, 'height' => 30]);
            }
        }
        unset($element);
        $preview = $this->postJson(self::EDITOR.'/preview', ['layout' => $layout])->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertDatabaseCount('spinotek_certificates', 1);
        $this->assertDatabaseCount('spinotek_certificate_designs', 0);
        $this->putJson(self::EDITOR, ['layout' => $layout])->assertNoContent();
        $this->assertEquals($layout, $this->get(self::EDITOR)->assertOk()->viewData('layout'));

        $new = app(CertificateGenerator::class)->issue($data);
        $this->assertSame($oldPdf, $old->fresh()->download()->getContent());
        $issuedImage = $this->pageImage($new->download()->getContent());
        $previewImage = $this->pageImage($preview->getContent());
        $url = (new QrReader($issuedImage, QrReader::SOURCE_TYPE_RESOURCE, false))->text(['TRY_HARDER' => true]);
        $this->assertSame('https://penerbit.test/certificates/verify/'.$new->code, $url);
        $this->get($url)->assertOk()->assertSeeText($data['participant_name'])->assertSeeText($data['activity_title']);

        // At 101.6 dpi, 1 mm occupies four pixels. Check both corners of the full-bleed paper.
        foreach ([[1, 1], [1185, 837], [32, 32]] as [$x, $y]) {
            $this->assertSame(0xf1e7cf, imagecolorat($issuedImage, $x, $y) & 0xffffff);
        }
        $margin = imagecolorsforindex($issuedImage, imagecolorat($issuedImage, 14, 710));
        $this->assertGreaterThan(245, min($margin['red'], $margin['green'], $margin['blue']));
        $foundNameInk = false;
        for ($y = 212; $y < 284; $y++) {
            for ($x = 720; $x < 840; $x++) {
                if ((imagecolorat($issuedImage, $x, $y) & 0xffffff) === 0xa5263c) {
                    $foundNameInk = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($foundNameInk, 'Nama berwarna harus berada di sisi kanan kotak yang dipindahkan.');

        foreach ([$issuedImage, $previewImage] as $image) {
            imagefilledrectangle($image, 10, 706, 134, 830, 0xffffff);
        }
        $this->assertSame($this->pngHash($previewImage), $this->pngHash($issuedImage));
    }

    public function test_editor_starts_from_the_host_template_and_persists_added_and_removed_elements(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));
        $published = json_decode(file_get_contents(__DIR__.'/../resources/templates/certificate.json'), true);
        $published['elements'][0]['text'] = 'Piagam dari aplikasi';
        $source = tmpfile();
        fwrite($source, json_encode($published, JSON_THROW_ON_ERROR));
        config(['certificate-generator.template' => stream_get_meta_data($source)['uri']]);

        $layout = $this->get(self::EDITOR)->assertOk()->viewData('layout');
        $this->assertSame($published, $layout);
        $layout['elements'] = array_values(array_filter($layout['elements'], fn ($el) => ! in_array($el['id'], ['recipient', 'recipient-label'])));
        $layout['elements'][] = ['id' => 'custom-label', 'type' => 'text', 'text' => 'Penerima penghargaan',
            'x' => 15, 'y' => 60, 'width' => 85, 'height' => 10, 'font_size' => 12];
        $layout['elements'][] = ['id' => 'custom-name', 'type' => 'variable', 'field' => 'participant_name',
            'x' => 15, 'y' => 72, 'width' => 200, 'height' => 20, 'font_size' => 22];
        $this->putJson(self::EDITOR, ['layout' => $layout])->assertNoContent();
        $this->assertSame($layout, $this->get(self::EDITOR)->viewData('layout'));
        $preview = $this->postJson(self::EDITOR.'/preview', ['layout' => $layout])->assertOk();
        $text = (new Process(['pdftotext', '-', '-']))->setInput($preview->getContent())->mustRun()->getOutput();
        $this->assertStringContainsString('Penerima penghargaan', $text);
        $this->assertStringContainsString('Rani Putri', $text);
        $this->assertStringNotContainsString('Diberikan kepada', $text);
        $this->assertSame($published, json_decode(file_get_contents(stream_get_meta_data($source)['uri']), true));
        fclose($source);
    }

    public static function invalidQr(): array
    {
        return [
            'hilang' => ['missing'], 'ganda' => ['duplicate'], 'terlalu kecil' => ['small'],
            'persegi panjang' => ['rectangle'], 'melewati tepi' => ['overflow'], 'posisi negatif' => ['negative'],
        ];
    }

    #[DataProvider('invalidQr')]
    public function test_invalid_qr_is_rejected_by_preview_and_save(string $case): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));
        $layout = $this->get(self::EDITOR)->viewData('layout');
        $index = array_search('qr', array_column($layout['elements'], 'type'), true);
        $qr = &$layout['elements'][$index];
        match ($case) {
            'missing' => array_splice($layout['elements'], $index, 1),
            'duplicate' => $layout['elements'][] = array_replace($qr, ['id' => 'second-qr']),
            'small' => $qr['width'] = $qr['height'] = 29,
            'rectangle' => $qr['height'] = 35,
            'overflow' => $qr['x'] = 290,
            'negative' => $qr['y'] = -1,
        };
        $this->postJson(self::EDITOR.'/preview', ['layout' => $layout])->assertUnprocessable();
        $this->putJson(self::EDITOR, ['layout' => $layout])->assertUnprocessable();
        $this->assertDatabaseCount('spinotek_certificate_designs', 0);
        $this->assertDatabaseCount('spinotek_certificates', 0);
    }

    public function test_jpeg_upload_and_invalid_backgrounds_or_properties(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));
        $this->post(self::EDITOR.'/background', ['background' => UploadedFile::fake()->image('paper.jpg')])->assertOk();
        $fake = UploadedFile::fake()->createWithContent('paper.png', 'This is not an image');
        $this->withHeader('Accept', 'application/json')->post(self::EDITOR.'/background', ['background' => $fake])->assertUnprocessable();
        $layout = $this->get(self::EDITOR)->viewData('layout');
        $layout['background'] = 'data:image/jpeg;base64,'.base64_encode('invalid');
        $this->putJson(self::EDITOR, ['layout' => $layout])->assertJsonValidationErrors('layout');
        unset($layout['background']);
        $layout['elements'][0]['color'] = 'red';
        $this->postJson(self::EDITOR.'/preview', ['layout' => $layout])->assertJsonValidationErrors('layout.elements.0.color');
    }

    private function backgroundPng(): string
    {
        $image = imagecreatetruecolor(40, 30);
        imagefill($image, 0, 0, 0xf1e7cf);
        ob_start();
        imagepng($image);

        return ob_get_clean();
    }

    private function pageImage(string $pdf): GdImage
    {
        $png = (new Process(['pdftoppm', '-png', '-singlefile', '-r', '101.6', '-']))
            ->setInput($pdf)->mustRun()->getOutput();

        return imagecreatefromstring($png);
    }

    private function pngHash(GdImage $image): string
    {
        ob_start();
        imagepng($image);

        return hash('sha256', ob_get_clean());
    }
}
