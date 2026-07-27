<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as RulesPassword;
class UserUpdateRequest extends FormRequest
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
         'name'=>'sometimes|string|max:50',
         'email'=>'sometimes|email|max:255|unique:users,email,'. $this->user_id,
         'password'=>[
            'sometimes',
            'string',
            RulesPassword::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols(),
            'confirmed',
         ]
    
        ];
    }
}
