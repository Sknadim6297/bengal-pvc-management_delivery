<?php

namespace App\Http\Requests;

use App\Support\IndianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class RegisterRequest extends FormRequest
{
    public const DISTRICTS = [
        'Alipurduar', 'Bankura', 'Paschim Bardhaman', 'Purba Bardhaman',
        'Birbhum', 'Cooch Behar', 'Dakshin Dinajpur', 'Darjeeling',
        'Hooghly', 'Howrah', 'Jalpaiguri', 'Jhargram', 'Kalimpong',
        'Kolkata', 'Maldah', 'Murshidabad', 'Nadia', 'North 24 Parganas',
        'South 24 Parganas', 'Uttar Dinajpur', 'Paschim Medinipur',
        'Purba Medinipur', 'Purulia', 'Siliguri Mahakuma Parishad',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'email' => Str::lower(trim((string) $this->input('email', ''))),
            'whatsapp_number' => IndianPhoneNumber::normalize((string) $this->input('whatsapp_number', '')),
            'district' => trim((string) $this->input('district', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => [
                'required',
                'string',
                'regex:/^91[6-9][0-9]{9}$/',
                'unique:users,whatsapp_number',
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'district' => ['required', Rule::in(self::DISTRICTS)],
            'password' => [
                'required',
                'string',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'whatsapp_number' => 'WhatsApp number',
        ];
    }
}
