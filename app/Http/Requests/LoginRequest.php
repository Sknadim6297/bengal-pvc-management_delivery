<?php

namespace App\Http\Requests;

use App\Support\IndianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $login = trim((string) $this->input('login', ''));
        $login = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? Str::lower($login)
            : IndianPhoneNumber::normalize($login);

        $this->merge(['login' => $login]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ];
    }
}
