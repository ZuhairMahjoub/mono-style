<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'price'=>'required|numeric|min:0',
            'description'=>'required|string',
            'category_id'=>'required|exists:categories,id',
            'name'=>'required|string|max:255',
            'image'=>'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
             'stock'=>'required|integer|min:0',
             'extra_categories'=>'array',
             'extra_categories.*'=>'exists:categories,id',
        ];
    }
}
