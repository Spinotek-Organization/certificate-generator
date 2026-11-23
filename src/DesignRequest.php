<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

class DesignRequest extends FormRequest
{
    public function rules(): array
    {
        $palettes = implode(',', [...array_keys(CertificateDesign::palettes()), 'custom']);

        return [
            'layout' => 'required|array:version,unit,page,frame,background,theme,elements',
            'layout.version' => 'required|integer|in:1',
            'layout.unit' => 'required|in:mm',
            'layout.page' => 'required|array:width,height',
            'layout.page.width' => 'required|numeric|between:30,1500',
            'layout.page.height' => 'required|numeric|between:30,1500',
            'layout.frame' => 'sometimes|boolean',
            'layout.background' => 'nullable|string|max:1398130',
            'layout.theme' => 'sometimes|array:palette,background,accent,frame',
            'layout.theme.palette' => 'sometimes|in:'.$palettes,
            'layout.theme.background' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.theme.accent' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.theme.frame' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.elements' => 'required|array|list|max:150',
            'layout.elements.*' => 'required|array:id,type,x,y,width,height,text,field,font_size,font,color,align,image,label',
            'layout.elements.*.id' => 'required|string|max:128|distinct:strict',
            'layout.elements.*.type' => 'required|in:text,variable,qr,image',
            'layout.elements.*.x' => 'required|numeric|between:0,1500',
            'layout.elements.*.y' => 'required|numeric|between:0,1500',
            'layout.elements.*.width' => 'required|numeric|gt:0|max:1500',
            'layout.elements.*.height' => 'required|numeric|gt:0|max:1500',
            'layout.elements.*.text' => 'required_if:layout.elements.*.type,text|string|max:2000',
            'layout.elements.*.field' => 'required_if:layout.elements.*.type,variable|in:participant_name,activity_title,issuer,issued_date,status',
            'layout.elements.*.font_size' => 'required_if:layout.elements.*.type,text,variable|numeric|between:1,200',
            'layout.elements.*.font' => 'sometimes|in:sans,serif,mono',
            'layout.elements.*.color' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.elements.*.align' => 'sometimes|in:left,center,right',
            'layout.elements.*.image' => 'required_if:layout.elements.*.type,image|prohibited_unless:layout.elements.*.type,image|string|max:1398130',
            'layout.elements.*.label' => 'nullable|string|max:80',
        ];
    }

    public function layout(): array
    {
        $layout = $this->validated('layout');
        try {
            CertificateDesign::checkGeometry($layout);
            CertificateDesign::imageSizes($layout);
        } catch (UnexpectedValueException $error) {
            throw ValidationException::withMessages(['layout' => $error->getMessage()]);
        }

        return $layout;
    }
}
