<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

class DesignRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'layout' => 'required|array:version,unit,page,frame,background,elements',
            'layout.version' => 'required|integer|in:1',
            'layout.unit' => 'required|in:mm',
            'layout.page' => 'required|array:width,height',
            'layout.page.width' => 'required|numeric|between:30,1500',
            'layout.page.height' => 'required|numeric|between:30,1500',
            'layout.frame' => 'sometimes|boolean',
            'layout.background' => 'nullable|string|max:1398130',
            'layout.elements' => 'required|array|list|max:150',
            'layout.elements.*' => 'required|array:id,type,x,y,width,height,text,field,font_size,font,color,align',
            'layout.elements.*.id' => 'required|string|max:128|distinct:strict',
            'layout.elements.*.type' => 'required|in:text,variable,qr',
            'layout.elements.*.x' => 'required|numeric|between:0,1500',
            'layout.elements.*.y' => 'required|numeric|between:0,1500',
            'layout.elements.*.width' => 'required|numeric|gt:0|max:1500',
            'layout.elements.*.height' => 'required|numeric|gt:0|max:1500',
            'layout.elements.*.text' => 'required_if:layout.elements.*.type,text|string|max:2000',
            'layout.elements.*.field' => 'required_if:layout.elements.*.type,variable|in:participant_name,activity_title,issuer,issued_date,status',
            'layout.elements.*.font_size' => 'required_unless:layout.elements.*.type,qr|numeric|between:1,200',
            'layout.elements.*.font' => 'sometimes|in:sans,serif,mono',
            'layout.elements.*.color' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.elements.*.align' => 'sometimes|in:left,center,right',
        ];
    }

    public function layout(): array
    {
        $layout = $this->validated('layout');
        try {
            CertificateDesign::checkGeometry($layout);
            if (isset($layout['background'])) {
                CertificateDesign::checkBackground($layout['background']);
            }
        } catch (UnexpectedValueException $error) {
            throw ValidationException::withMessages(['layout' => $error->getMessage()]);
        }

        return $layout;
    }
}
