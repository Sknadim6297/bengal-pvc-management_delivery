<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePricingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'normal_single_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'normal_2_5_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'normal_2_5_discount' => ['required', 'integer', 'min:0', 'max:100'],
            'normal_6_7_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'normal_6_7_discount' => ['required', 'integer', 'min:0', 'max:100'],
            'normal_8_10_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'normal_8_10_discount' => ['required', 'integer', 'min:0', 'max:100'],
            'normal_11_plus_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'normal_11_plus_discount' => ['required', 'integer', 'min:0', 'max:100'],
            'premium_under_12_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'premium_12_plus_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'shipping_fee' => ['required', 'integer', 'min:0', 'max:100000'],
            'free_shipping_minimum_quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'card_cover_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'photo_shipping_fee' => ['sometimes', 'required', 'numeric', 'decimal:0,2', 'min:0', 'max:100000'],
            'photo_options' => ['sometimes', 'array', 'max:50'],
            'photo_options.*.id' => ['nullable', 'integer'],
            'photo_options.*.name' => ['required_with:photo_options', 'string', 'max:120'],
            'photo_options.*.slug' => ['required_with:photo_options', 'string', 'alpha_dash', 'max:40', 'distinct'],
            'photo_options.*.description' => ['nullable', 'string', 'max:255'],
            'photo_options.*.unit_price' => ['required_with:photo_options', 'numeric', 'decimal:0,2', 'min:0', 'max:100000'],
            'photo_options.*.enabled' => ['required_with:photo_options', 'boolean'],
            'photo_options.*.display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $photoServiceId = \App\Models\PrintService::query()->where('slug', 'photo-print')->value('id');

            foreach ($this->input('photo_options', []) as $index => $option) {
                if (empty($option['slug']) || ! $photoServiceId) {
                    continue;
                }

                $duplicate = \App\Models\PrintQuality::query()
                    ->where('service_id', $photoServiceId)
                    ->where('slug', $option['slug'])
                    ->when(! empty($option['id']), fn ($query) => $query->where('id', '!=', $option['id']))
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add("photo_options.{$index}.slug", 'That photo option slug is already in use.');
                }
            }
        }];
    }
}