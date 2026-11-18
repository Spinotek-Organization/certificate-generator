<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use UnexpectedValueException;

class DesignEditor
{
    public function edit(): View
    {
        return view('spinotek-certificates::editor', [
            'layout' => CertificateDesign::current(),
            'sample' => $this->sample(),
            'qr' => base64_encode(CertificatePdf::qrImage(route('spinotek.design.edit'))),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $file = $request->validate(['background' => 'required|file|max:1024|mimetypes:image/jpeg,image/png'])['background'];
        $uri = 'data:'.$file->getMimeType().';base64,'.base64_encode($file->getContent());
        try {
            CertificateDesign::checkBackground($uri);
        } catch (UnexpectedValueException $error) {
            throw ValidationException::withMessages(['background' => $error->getMessage()]);
        }

        return response()->json(['background' => $uri]);
    }

    public function preview(DesignRequest $request, CertificatePdf $renderer): Response
    {
        $pdf = $renderer->render($this->sample(), route('spinotek.design.edit'), $request->layout());

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pratinjau.pdf"',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function save(DesignRequest $request): Response
    {
        CertificateDesign::save($request->layout());

        return response()->noContent();
    }

    private function sample(): array
    {
        return [
            'participant_name' => 'Rani Putri',
            'activity_title' => 'Lokakarya Desain Informasi',
            'issued_date' => today()->toDateString(),
            'issuer' => config('app.name'),
            'status' => 'Valid',
        ];
    }
}
