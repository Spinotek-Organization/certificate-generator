<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spinotek\CertificateGenerator\Support\VariableRegistry;
use UnexpectedValueException;

class DesignRequest extends FormRequest
{
    public function rules(): array
    {
        $palettes = implode(',', [...array_keys(CertificateDesign::palettes()), 'custom']);

        $rawLayout = $this->input('layout', []);
        $allowedFields = array_keys(VariableRegistry::all(is_array($rawLayout) ? $rawLayout : null));

        return [
            'name' => 'sometimes|string|max:120',
            'category' => 'nullable|string|max:64',
            'is_default' => 'sometimes|boolean',
            'number_format' => 'nullable|string|max:120',
            'layout' => 'required|array:version,unit,page,frame,background,theme,custom_variables,elements',
            'layout.version' => 'required|integer|in:1',
            'layout.unit' => 'required|in:mm',
            'layout.page' => 'required|array:width,height',
            'layout.page.width' => 'required|numeric|between:30,1500',
            'layout.page.height' => 'required|numeric|between:30,1500',
            'layout.frame' => 'sometimes|boolean',
            'layout.background' => 'nullable|string|max:7340032',
            'layout.theme' => 'sometimes|array:palette,background,accent,frame',
            'layout.theme.palette' => 'sometimes|in:'.$palettes,
            'layout.theme.background' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.theme.accent' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.theme.frame' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.custom_variables' => 'sometimes|array|max:30',
            'layout.custom_variables.*' => 'required|array:key,label,sample',
            'layout.custom_variables.*.key' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/'],
            'layout.custom_variables.*.label' => 'required|string|max:80',
            'layout.custom_variables.*.sample' => 'nullable|string|max:120',
            'layout.elements' => 'required|array|list|max:150',
            'layout.elements.*' => 'required|array:id,type,x,y,width,height,text,field,font_size,font,color,align,image,label',
            'layout.elements.*.id' => 'required|string|max:128|distinct:strict',
            'layout.elements.*.type' => 'required|in:text,variable,qr,image',
            'layout.elements.*.x' => 'required|numeric|between:0,1500',
            'layout.elements.*.y' => 'required|numeric|between:0,1500',
            'layout.elements.*.width' => 'required|numeric|gt:0|max:1500',
            'layout.elements.*.height' => 'required|numeric|gt:0|max:1500',
            'layout.elements.*.text' => 'required_if:layout.elements.*.type,text|string|max:2000',
            'layout.elements.*.field' => ['required_if:layout.elements.*.type,variable', 'string', Rule::in($allowedFields)],
            'layout.elements.*.font_size' => 'required_if:layout.elements.*.type,text,variable|numeric|between:1,200',
            'layout.elements.*.font' => 'sometimes|in:sans,serif,mono',
            'layout.elements.*.color' => ['sometimes', 'string', 'regex:/^#[a-f0-9]{6}$/iD'],
            'layout.elements.*.align' => 'sometimes|in:left,center,right',
            'layout.elements.*.image' => 'required_if:layout.elements.*.type,image|prohibited_unless:layout.elements.*.type,image|string|max:7340032',
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
