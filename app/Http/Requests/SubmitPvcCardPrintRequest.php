<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\GoogleDriveShareUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitPvcCardPrintRequest extends FormRequest
{
    private const MAX_TOTAL_PDF_BYTES = 80 * 1024 * 1024;

    public function authorize(): bool
    {
        return $this->user() instanceof User && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'pdf_files' => ['sometimes', 'array', 'max:100'],
            'pdf_files.*' => ['file', 'mimes:pdf', 'extensions:pdf', 'max:81920'],
            'drive_links' => ['sometimes', 'array', 'max:100'],
            'drive_links.*' => [
                'required',
                'url',
                'max:2048',
                'distinct',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! GoogleDriveShareUrl::isValid($value)) {
                        $fail('Enter a valid Google Drive or Google Docs share URL.');
                    }
                },
            ],
            'print_quality' => ['required', 'in:normal,premium'],
            'include_card_cover' => ['sometimes', 'boolean'],
            'submission_key' => ['nullable', 'uuid'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $files = array_filter($this->file('pdf_files', []));
            $totalBytes = array_sum(array_map(static fn ($file): int => (int) $file->getSize(), $files));

            if ($totalBytes > self::MAX_TOTAL_PDF_BYTES) {
                $validator->errors()->add('pdf_files', 'PDF files must total no more than 80 MB.');
            }

            if ($files === [] && $this->input('drive_links', []) === []) {
                $validator->errors()->add('pdf_files', 'Add at least one PDF file or Google Drive link.');
            }
        }];
    }
}