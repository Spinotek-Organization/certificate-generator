<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spinotek\CertificateGenerator\Models\CertificateDesignRecord;
use Spinotek\CertificateGenerator\Support\BackgroundPresetRegistry;
use Spinotek\CertificateGenerator\Support\VariableRegistry;
use UnexpectedValueException;

class DesignEditor
{
    public function index(): View
    {
        CertificateDesign::ensureDefaultRecord();

        return view('spinotek-certificates::designs.index', [
            'designs' => CertificateDesign::all(),
            'categories' => config('certificate-generator.categories', ['general' => 'Umum']),
            'defaultNumberFormat' => config('certificate-generator.number.format', 'CERT/{CAT}/{YYYY}/{SEQ:5}'),
            'backgroundPresets' => BackgroundPresetRegistry::all(),
            'backUrl' => config('certificate-generator.admin_back_url', '/'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:64',
            'number_format' => 'nullable|string|max:120',
            'is_default' => 'sometimes|boolean',
            'starter_mode' => 'sometimes|in:blank,default',
            'preset_background' => 'nullable|string|max:64',
        ]);

        $starterMode = $validated['starter_mode'] ?? 'default';
        $preset = ! empty($validated['preset_background'])
            ? BackgroundPresetRegistry::find($validated['preset_background'])
            : null;

        if ($preset !== null) {
            $page = CertificateDesign::pageSizeForImage($preset['width'], $preset['height']);
            if ($starterMode === 'blank') {
                $layout = CertificateDesign::blankLayout($page['width'], $page['height'], $preset['uri']);
            } else {
                $layout = CertificateDesign::defaultLayout();
                $layout['page'] = $page;
                $layout['background'] = $preset['uri'];
                $layout['frame'] = false;
                $layout = CertificateDesign::clampElementsToPage($layout);
            }
        } else {
            $layout = $starterMode === 'blank'
                ? CertificateDesign::blankLayout()
                : CertificateDesign::defaultLayout();
        }

        $validated['layout'] = $layout;
        $design = CertificateDesign::createTemplate($validated);

        return redirect()->route('spinotek.design.edit', $design);
    }

    public function edit(?CertificateDesignRecord $design = null): View
    {
        $design ??= CertificateDesign::ensureDefaultRecord();
        $layout = $design->layout ?: CertificateDesign::defaultLayout();

        $presets = array_map(static fn (array $preset): array => [
            'key' => $preset['key'],
            'label' => $preset['label'],
            'width' => $preset['width'],
            'height' => $preset['height'],
            'preview_url' => route('spinotek.design.preset', ['key' => $preset['key']]),
            'data_url' => route('spinotek.design.preset', ['key' => $preset['key'], 'format' => 'json']),
        ], BackgroundPresetRegistry::all());

        return view('spinotek-certificates::editor', [
            'design' => $design,
            'designs' => CertificateDesign::all(),
            'categories' => config('certificate-generator.categories', ['general' => 'Umum']),
            'layout' => $layout,
            'palettes' => CertificateDesign::palettes(),
            'frameLines' => CertificateDesign::frameLines($layout['page']['width'], $layout['page']['height']),
            'variables' => VariableRegistry::all($layout),
            'sample' => VariableRegistry::sampleValues($layout),
            'backgroundPresets' => $presets,
            'meta' => [
                'name' => $design->name,
                'category' => $design->category ?? 'general',
                'is_default' => (bool) $design->is_default,
                'number_format' => $design->number_format ?? config('certificate-generator.number.format', 'CERT/{CAT}/{YYYY}/{SEQ:5}'),
            ],
            'qr' => base64_encode(CertificatePdf::qrImage(route('spinotek.certificates.verify.index'))),
            'backUrl' => route('spinotek.design.index'),
        ]);
    }

    public function preset(Request $request, string $key): Response|JsonResponse
    {
        $preset = BackgroundPresetRegistry::find($key);
        abort_if($preset === null, 404);

        if ($request->query('format') === 'json' || $request->expectsJson()) {
            return response()->json([
                'background' => $preset['uri'],
                'width' => $preset['width'],
                'height' => $preset['height'],
                'label' => $preset['label'],
            ]);
        }

        return response((string) file_get_contents($preset['path']), 200, [
            'Content-Type' => $preset['mime'],
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $maxKb = (int) config('certificate-generator.upload.max_kb', 5120);
        $file = $request->validate([
            'background' => 'required|file|max:'.$maxKb.'|mimetypes:image/jpeg,image/png',
        ])['background'];

        $uri = 'data:'.$file->getMimeType().';base64,'.base64_encode($file->getContent());
        try {
            [$width, $height] = CertificateDesign::checkBackground($uri);
        } catch (UnexpectedValueException $error) {
            throw ValidationException::withMessages(['background' => $error->getMessage()]);
        }

        return response()->json([
            'background' => $uri,
            'width' => $width,
            'height' => $height,
        ]);
    }

    public function preview(DesignRequest $request, CertificatePdf $renderer): Response
    {
        $layout = $request->layout();
        $sample = VariableRegistry::sampleValues($layout);
        $pdf = $renderer->render($sample, route('spinotek.certificates.verify.index'), $layout);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pratinjau.pdf"',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function save(DesignRequest $request, ?CertificateDesignRecord $design = null): Response
    {
        $design ??= CertificateDesign::ensureDefaultRecord();

        CertificateDesign::updateTemplate($design, [
            'name' => $request->input('name', $design->name),
            'category' => $request->has('category') ? $request->input('category') : $design->category,
            'is_default' => $request->has('is_default') ? $request->boolean('is_default') : $design->is_default,
            'number_format' => $request->has('number_format') ? $request->input('number_format') : $design->number_format,
            'layout' => $request->layout(),
        ]);

        return response()->noContent();
    }

    public function duplicate(CertificateDesignRecord $design): RedirectResponse
    {
        $copy = CertificateDesign::createTemplate([
            'name' => $design->name.' (Salinan)',
            'category' => $design->category,
            'is_default' => false,
            'number_format' => $design->number_format,
            'layout' => $design->layout,
        ]);

        return redirect()->route('spinotek.design.edit', $copy);
    }

    public function setDefault(CertificateDesignRecord $design): RedirectResponse
    {
        CertificateDesign::updateTemplate($design, ['is_default' => true]);

        return redirect()->route('spinotek.design.index');
    }

    public function destroy(CertificateDesignRecord $design): RedirectResponse
    {
        if (CertificateDesignRecord::query()->count() <= 1) {
            return redirect()->route('spinotek.design.index')->with('error', 'Minimal harus ada 1 template sertifikat.');
        }

        $design->delete();

        return redirect()->route('spinotek.design.index');
    }
}
