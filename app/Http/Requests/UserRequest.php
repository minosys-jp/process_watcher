<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
            'email' => ['required', 'email', 'unique:users'],
            'name' => ['required'],
            'password' => ['required', 'confirmed', 'min:8']
        ];
    }

    public function message() {
        return [
            'email.required' => ':attributeは必須項目です',
            'email.email' => ':attributeは電子メール形式です',
            'email.unique' => ':attributeは既に定義されています',
            'name.required' => ':attributeは必須項目です',
            'password.required' => ':attributeは必須項目です',
            'password.confirmed' => ':attributeが一致しません',
            'password.min' => ':attributeは最低8文字以上指定してください',
        ];
    }

    public function attributes() {
        return [
            'email' => '電子メール',
            'name' => '名前',
            'password' => 'パスワード',
        ];
    }
}
