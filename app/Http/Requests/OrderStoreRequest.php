<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            
           // 'total_price' => ['required', 'numeric', 'min:0'],
           // 'status' => ['nullable', 'string'],
        ];

        
        if (!Auth::check()) {
            $rules['customer_name'] = ['required', 'string', 'max:255'];
            $rules['customer_email'] = ['required', 'email', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'يجب ادخال اسم العميل اذا لم يكن المستخدم مسجلا',
            'customer_email.required' => 'يجب اخال البريد الالكتروني اذا لم يكن المستخدم مسجلا',
            'products.required' => 'يجب اختيار منتج واحد على الأقل',
        ];
    }
}
