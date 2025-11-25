<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price'         => 'sometimes|numeric|min:0',
            'description'   => 'sometimes|string',
            'category_id'   => 'sometimes|exists:categories,id',
            'name'          => 'sometimes|string|max:255',
            'image'         => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'stock'         => 'sometimes|integer|min:0',

            // الفئات الإضافية
            'extra_categories'     => 'sometimes|array',
            'extra_categories.*'   => 'sometimes|integer|exists:categories,id',
        ];
    }
}
