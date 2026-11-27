<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spinotek\CertificateGenerator\CertificateDesign;
use Spinotek\CertificateGenerator\CertificateGenerator;
use Spinotek\CertificateGenerator\Models\CertificateDesignRecord;
use Spinotek\CertificateGenerator\Support\BackgroundPresetRegistry;
use Symfony\Component\Process\Process;
use Zxing\QrReader;

final class DesignEditorTest extends TestCase
{
    private const CREATOR = '/certificates/design';
    private const EDITOR = '/certificates/design/editor';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        $this->artisan('migrate')->assertSuccessful();
        Gate::define('manage-certificate-design', fn (Authenticatable $user) => $user->getAuthIdentifier() === 1);
        BackgroundPresetRegistry::clearRuntime();
    }

    protected function tearDown(): void
    {
        BackgroundPresetRegistry::clearRuntime();
        parent::tearDown();
    }

    public function test_host_gate_blocks_guests_and_non_admins_on_all_editor_endpoints(): void
    {
        // Unauthenticated requests are rejected by auth middleware.
        $this->getJson(self::CREATOR)->assertUnauthorized();
        $this->postJson(self::CREATOR, ['name' => 'Blocked'])->assertUnauthorized();
        $this->getJson(self::EDITOR)->assertUnauthorized();
        $this->postJson(self::EDITOR.'/background')->assertUnauthorized();
        $this->postJson(self::EDITOR.'/preview')->assertUnauthorized();
        $this->putJson(self::EDITOR)->assertUnauthorized();

        // Authenticated non-admins are blocked by the manage-certificate-design gate.
        $this->actingAs(new GenericUser(['id' => 2]));
        $this->get(self::CREATOR)->assertForbidden();
        $this->post(self::CREATOR, ['name' => 'Blocked'])->assertForbidden();
        $this->get(self::EDITOR)->assertForbidden();
        $this->postJson(self::EDITOR.'/background')->assertForbidden();
        $this->postJson(self::EDITOR.'/preview')->assertForbidden();
        $this->putJson(self::EDITOR)->assertForbidden();
    }

    public function test_multi_template_creator_blank_mode_presets_duplicate_and_destroy_guard(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $presetDir = sys_get_temp_dir().'/spinotek-preset-'.Str::uuid();
        File::makeDirectory($presetDir);
        $presetFile = $presetDir.'/base.png';
        File::put($presetFile, $this->backgroundPng(80, 60));

        try {
            BackgroundPresetRegistry::register('spinotek-gold', 'Spinotek Gold', $presetFile);

            $this->get(self::CREATOR)->assertOk()->assertSeeText('Certificate Creator');
            $this->assertSame(1, CertificateDesignRecord::query()->count());

            // Delete blocked when only 1 template exists.
            $first = CertificateDesignRecord::query()->firstOrFail();
            $this->delete(self::CREATOR.'/'.$first->id)->assertRedirect(self::CREATOR);
            $this->assertSame(1, CertificateDesignRecord::query()->count());

            // Create new course template in blank mode with preset background.
            $response = $this->post(self::CREATOR, [
                'name' => 'Sertifikat Course Blank',
                'category' => 'course',
                'number_format' => 'CRS/{YYYY}/{SEQ:4}',
                'starter_mode' => 'blank',
                'preset_background' => 'spinotek-gold',
                'is_default' => 1,
            ]);

            $created = CertificateDesignRecord::query()->where('name', 'Sertifikat Course Blank')->firstOrFail();
            $response->assertRedirect(route('spinotek.design.edit', $created));
            $this->assertCount(1, $created->layout['elements']);
            $this->assertSame('qr', $created->layout['elements'][0]['type']);
            $this->assertStringStartsWith('data:image/png;base64,', $created->layout['background']);

            // Preset endpoint returns JSON and raw image.
            $this->getJson(self::EDITOR.'/preset/spinotek-gold?format=json')
                ->assertOk()
                ->assertJsonPath('label', 'Spinotek Gold');
            $this->get(self::EDITOR.'/preset/spinotek-gold')
                ->assertOk()
                ->assertHeader('Content-Type', 'image/png');

            // Duplicate and delete work cleanly.
            $this->post(self::CREATOR.'/'.$created->id.'/duplicate')->assertRedirect();
            $this->assertSame(3, CertificateDesignRecord::query()->count());

            $this->delete(self::CREATOR.'/'.$created->id)->assertRedirect(self::CREATOR);
            $this->assertSame(2, CertificateDesignRecord::query()->count());
        } finally {
            File::deleteDirectory($presetDir);
        }
    }

    public function test_edited_design_survives_reload_and_matches_issuance_outside_the_unique_qr(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $editor = $this->get(self::EDITOR)->assertOk()->assertSeeText('Simpan desain');
        $design = $editor->viewData('design');
        $layout = $editor->viewData('layout');
        $layout['frame'] = false;

        $upload = $this->post(self::EDITOR.'/background', [
            'background' => UploadedFile::fake()->createWithContent('latar.png', $this->backgroundPng()),
        ])->assertOk();
        $layout['background'] = $upload->json('background');

        $layout['elements'][] = [
            'id' => 'judul_custom',
            'type' => 'text',
            'text' => 'PIAGAM PENGHARGAAN',
            'x' => 18,
            'y' => 16,
            'width' => 180,
            'height' => 10,
            'font_size' => 18,
            'font' => 'sans',
            'color' => '#0b254b',
            'align' => 'left',
        ];

        $preview = $this->postJson(self::EDITOR.'/preview', ['layout' => $layout])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-1.', $preview->getContent());

        $this->putJson(self::EDITOR.'/'.$design->id, [
            'name' => 'Template Teruji',
            'category' => 'general',
            'is_default' => true,
            'number_format' => 'CERT/{CAT}/{YYYY}/{SEQ:5}',
            'layout' => $layout,
        ])->assertNoContent();

        $reloaded = $this->get(self::EDITOR.'/'.$design->id)->assertOk();
        $this->assertEquals($layout, $reloaded->viewData('layout'));

        $issued = app(CertificateGenerator::class)->issue($editor->viewData('sample'));
        $issuedPdf = $issued->download()->getContent();
        $this->assertStringStartsWith('%PDF-1.', $issuedPdf);

        if ($this->hasPoppler()) {
            $previewImg = $this->pageImage($preview->getContent());
            $issuedImg = $this->pageImage($issuedPdf);
            $qr = collect($layout['elements'])->firstWhere('type', 'qr');
            $white = imagecolorallocate($previewImg, 255, 255, 255);
            imagefilledrectangle($previewImg, (int) ($qr['x'] * 4) - 4, (int) ($qr['y'] * 4) - 4, (int) (($qr['x'] + $qr['width']) * 4) + 4, (int) (($qr['y'] + $qr['height']) * 4) + 4, $white);
            imagefilledrectangle($issuedImg, (int) ($qr['x'] * 4) - 4, (int) ($qr['y'] * 4) - 4, (int) (($qr['x'] + $qr['width']) * 4) + 4, (int) (($qr['y'] + $qr['height']) * 4) + 4, $white);
            $this->assertSame($this->pngHash($previewImg), $this->pngHash($issuedImg));

            $page = (new Process(['pdftoppm', '-r', '110', '-png', '-singlefile', '-']))
                ->setInput($preview->getContent())->mustRun()->getOutput();
            $this->assertSame('https://penerbit.test/certificates/verify', (new QrReader($page, QrReader::SOURCE_TYPE_BLOB, false))->text(['TRY_HARDER' => true]));
        }
    }

    public function test_editor_persists_custom_variables_and_added_or_removed_elements(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $editor = $this->get(self::EDITOR)->assertOk();
        $design = $editor->viewData('design');
        $layout = $editor->viewData('layout');

        $layout['custom_variables'] = [
            ['key' => 'grade', 'label' => 'Nilai Akhir', 'sample' => 'A (Cumlaude)'],
        ];
        $layout['elements'][] = [
            'id' => 'var_grade',
            'type' => 'variable',
            'field' => 'grade',
            'x' => 18,
            'y' => 150,
            'width' => 120,
            'height' => 10,
            'font_size' => 12,
            'font' => 'sans',
            'color' => '#0b254b',
            'align' => 'left',
        ];

        $this->putJson(self::EDITOR.'/'.$design->id, ['layout' => $layout])->assertNoContent();
        $this->assertEquals($layout, $this->get(self::EDITOR.'/'.$design->id)->assertOk()->viewData('layout'));

        // Remove the custom variable and element.
        array_pop($layout['elements']);
        $layout['custom_variables'] = [];
        $this->putJson(self::EDITOR.'/'.$design->id, ['layout' => $layout])->assertNoContent();
        $this->assertEquals($layout, $this->get(self::EDITOR.'/'.$design->id)->assertOk()->viewData('layout'));
    }

    public static function invalidQr(): array
    {
        return [
            'missing' => ['missing'],
            'duplicate' => ['duplicate'],
            'non_square' => ['non_square'],
            'small' => ['small'],
        ];
    }

    #[DataProvider('invalidQr')]
    public function test_invalid_qr_is_rejected_by_preview_and_save(string $case): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));
        $layout = CertificateDesign::defaultLayout();

        $qrIndex = array_search('qr', array_column($layout['elements'], 'type'), true);
        if ($case === 'missing') {
            array_splice($layout['elements'], $qrIndex, 1);
        } elseif ($case === 'duplicate') {
            $dup = $layout['elements'][$qrIndex];
            $dup['id'] = 'qr_2';
            $dup['x'] = 18;
            $layout['elements'][] = $dup;
        } elseif ($case === 'non_square') {
            $layout['elements'][$qrIndex]['width'] = 45;
        } else {
            $layout['elements'][$qrIndex]['width'] = 24;
            $layout['elements'][$qrIndex]['height'] = 24;
        }

        $this->postJson(self::EDITOR.'/preview', ['layout' => $layout])->assertUnprocessable();
        $this->putJson(self::EDITOR, ['layout' => $layout])->assertUnprocessable();
    }

    public function test_jpeg_upload_and_invalid_backgrounds_or_properties(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $jpeg = $this->jpegImage();
        $this->post(self::EDITOR.'/background', [
            'background' => UploadedFile::fake()->createWithContent('latar.jpg', $jpeg),
        ])->assertOk()->assertJsonPath('width', 40);

        $this->postJson(self::EDITOR.'/background', [
            'background' => UploadedFile::fake()->create('bad.txt', 10, 'text/plain'),
        ])->assertUnprocessable();

        $layout = CertificateDesign::defaultLayout();
        $badBg = $layout;
        $badBg['background'] = 'data:image/jpeg;base64,invalid';
        $this->putJson(self::EDITOR, ['layout' => $badBg])->assertUnprocessable();

        $badColor = $layout;
        $badColor['elements'][0]['color'] = 'blue';
        $this->putJson(self::EDITOR, ['layout' => $badColor])->assertUnprocessable();
    }

    public function test_saved_palette_custom_theme_colors_and_legacy_layout_compatibility(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $layout = CertificateDesign::defaultLayout();
        $layout['theme'] = [
            'palette' => 'spinotek-blue',
            'background' => '#f8fbff',
            'accent' => '#0088ff',
            'frame' => '#0b254b',
        ];

        $this->putJson(self::EDITOR, ['layout' => $layout])->assertNoContent();
        $this->assertEquals('spinotek-blue', $this->get(self::EDITOR)->assertOk()->viewData('layout')['theme']['palette']);

        // Legacy layout without theme/custom_variables still renders cleanly.
        $legacy = [
            'version' => 1,
            'unit' => 'mm',
            'page' => ['width' => 297, 'height' => 210],
            'frame' => true,
            'background' => null,
            'elements' => $layout['elements'],
        ];
        $this->postJson(self::EDITOR.'/preview', ['layout' => $legacy])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_uploaded_image_element_keeps_its_proportions_in_preview_and_issuance(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $editor = $this->get(self::EDITOR)->assertOk();
        $design = $editor->viewData('design');
        $layout = $editor->viewData('layout');
        $layout['frame'] = false;
        $layout['background'] = 'data:image/png;base64,'.base64_encode($this->backgroundPng());

        $image = 'data:image/png;base64,'.base64_encode($this->backgroundPng(20, 10, 0x0052cc));
        $layout['elements'][] = [
            'id' => 'logo',
            'type' => 'image',
            'label' => 'Logo Spinotek',
            'x' => 18,
            'y' => 47,
            'width' => 40,
            'height' => 40,
            'image' => $image,
        ];

        $invalid = $layout;
        $invalid['elements'][array_key_last($invalid['elements'])]['image'] = 'data:image/png;base64,invalid';
        $this->putJson(self::EDITOR, ['layout' => $invalid])->assertUnprocessable();

        $preview = $this->postJson(self::EDITOR.'/preview', ['layout' => $layout])->assertOk();
        $this->putJson(self::EDITOR.'/'.$design->id, ['layout' => $layout])->assertNoContent();
        $this->assertEquals($layout, $this->get(self::EDITOR.'/'.$design->id)->assertOk()->viewData('layout'));

        $issued = app(CertificateGenerator::class)->issue($editor->viewData('sample'));
        $this->assertStringStartsWith('%PDF-1.', $issued->download()->getContent());

        if ($this->hasPoppler()) {
            foreach ([$preview->getContent(), $issued->download()->getContent()] as $pdf) {
                $rendered = $this->pageImage($pdf);
                $this->assertSame(0xf1e7cf, imagecolorat($rendered, 100, 220) & 0xffffff);
                $this->assertNotSame(0xf1e7cf, imagecolorat($rendered, 100, 192) & 0xffffff);
            }
        }
    }

    private function backgroundPng(int $w = 40, int $h = 30, int $color = 0xf1e7cf): string
    {
        $image = imagecreatetruecolor($w, $h);
        imagefill($image, 0, 0, $color);
        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    private function jpegImage(): string
    {
        $image = imagecreatetruecolor(40, 30);
        imagefill($image, 0, 0, 0xf8fbff);
        ob_start();
        imagejpeg($image, null, 90);

        return (string) ob_get_clean();
    }

    private function hasPoppler(): bool
    {
        return trim((string) @shell_exec('command -v pdftoppm')) !== '';
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

        return hash('sha256', (string) ob_get_clean());
    }
}
