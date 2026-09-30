<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'application_name' => ['required', 'string', 'max:120'],
            'brand_name' => ['required', 'string', 'max:120'],
            'helpline_number' => ['required', 'string', 'max:32', 'regex:/^[0-9+().\s-]+$/'],
            'whatsapp_contact_url' => ['nullable', 'string', 'url', 'regex:/^https?:\/\//i', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2500,max_height=2500'],
            'favicon' => ['nullable', 'image', 'mimes:png,webp', 'max:1024', 'dimensions:max_width=512,max_height=512'],
        ];
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        throw new HttpResponseException(
            back()
                ->with('toast_error', $validator->errors()->first())
                ->withInput($this->except(['logo', 'favicon']))
        );
    }
}