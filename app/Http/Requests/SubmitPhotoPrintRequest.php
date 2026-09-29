<?php

namespace App\Http\Requests;

use App\Models\PrintService;
use App\Models\User;
use App\Support\GoogleDriveShareUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class SubmitPhotoPrintRequest extends FormRequest
{
    public const DISTRICTS = [
        'Kolkata',
        'Howrah',
        'Hooghly',
        'North 24 Parganas',
        'South 24 Parganas',
        'Purba Medinipur',
        'Paschim Medinipur',
    ];

    private const MAX_TOTAL_IMAGE_BYTES = 80 * 1024 * 1024;

    public function authorize(): bool
    {
        return $this->user() instanceof User && $this->user()->isActive();
    }

    public function rules(): array
    {
        $photoServiceId = PrintService::query()->where('slug', 'photo-print')->value('id');

        return [
            'photo_option_id' => [
                'required',
                'integer',
                Rule::exists('print_qualities', 'id')->where(fn ($query) => $query
                    ->where('service_id', $photoServiceId)
                    ->where('enabled', true)),
            ],
            'photo_files' => ['sometimes', 'array', 'max:100'],
            'photo_files.*' => ['file', 'mimes:jpg,jpeg,png', 'extensions:jpg,jpeg,png', 'max:81920'],
            'drive_links' => ['sometimes', 'array', 'max:100'],
            'drive_links.*' => [
                'required',
                'url',
                'max:2048',
                'distinct',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! GoogleDriveShareUrl::isValid($value)) {
                        $fail('Enter a valid HTTPS Google Drive or Google Photos share URL.');
                    }
                },
            ],
            'delivery_district' => ['required', Rule::in(self::DISTRICTS)],
            'delivery_address.village_area' => ['nullable', 'string', 'max:255'],
            'delivery_address.landmark' => ['nullable', 'string', 'max:255'],
            'delivery_address.post_office' => ['nullable', 'string', 'max:255'],
            'delivery_address.police_station' => ['nullable', 'string', 'max:255'],
            'delivery_address.pincode' => ['nullable', 'digits:6'],
            'submission_key' => ['nullable', 'uuid'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $files = array_filter($this->file('photo_files', []));
            $totalBytes = array_sum(array_map(static fn ($file): int => (int) $file->getSize(), $files));

            if ($totalBytes > self::MAX_TOTAL_IMAGE_BYTES) {
                $validator->errors()->add('photo_files', 'Photo files must total no more than 80 MB.');
            }

            $hashes = [];
            foreach ($files as $index => $file) {
                if ((int) $file->getSize() < 1) {
                    $validator->errors()->add("photo_files.{$index}", 'Empty photo files are not allowed.');
                    continue;
                }

                $hash = hash_file('sha256', $file->getRealPath());
                if (isset($hashes[$hash])) {
                    $validator->errors()->add("photo_files.{$index}", 'Duplicate photo files are not allowed.');
                }
                $hashes[$hash] = true;
            }

            if ($files === [] && $this->input('drive_links', []) === []) {
                $validator->errors()->add('photo_files', 'Add at least one photo file or Google Drive link.');
            }
        }];
    }
}