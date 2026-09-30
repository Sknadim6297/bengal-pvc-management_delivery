<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class GeneralSettings
{
    private ?GeneralSetting $settings = null;

    public function get(): GeneralSetting
    {
        return $this->settings ??= $this->load();
    }

    private function load(): GeneralSetting
    {
        try {
            $settings = GeneralSetting::query()->find(1);
        } catch (QueryException $exception) {
            if (Schema::hasTable('general_settings')) {
                throw $exception;
            }
            $settings = null;
        }

        if ($settings !== null) {
            return $settings;
        }

        $settings = new GeneralSetting;
        $settings->id = 1;
        $settings->application_name = 'India PVC';
        $settings->brand_name = 'Bengal PVC';
        $settings->helpline_number = '+91 8900162634';

        return $settings;
    }
}