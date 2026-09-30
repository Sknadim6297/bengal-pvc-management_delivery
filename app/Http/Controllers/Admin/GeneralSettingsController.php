<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Models\GeneralSetting;
use App\Services\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class GeneralSettingsController extends Controller
{
    public function edit(GeneralSettings $generalSettings): View
    {
        return view('admin-panel.general-settings.edit', [
            'settings' => $generalSettings->get(),
        ]);
    }

    public function update(UpdateGeneralSettingsRequest $request, GeneralSettings $generalSettings): RedirectResponse
    {
        $values = $request->validated();
        $settings = GeneralSetting::query()->findOrFail(1);
        $previousPaths = ['logo' => $settings->logo_path, 'favicon' => $settings->favicon_path];
        $newPaths = [];

        try {
            foreach (['logo', 'favicon'] as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('general-settings', 'public');
                    if (! is_string($path)) {
                        throw new RuntimeException('The uploaded image could not be stored.');
                    }
                    $newPaths[$field] = $path;
                }
            }

            DB::transaction(function () use ($settings, $values, $newPaths): void {
                $settings->application_name = $values['application_name'];
                $settings->brand_name = $values['brand_name'];
                $settings->helpline_number = $values['helpline_number'];
                $settings->whatsapp_contact_url = $values['whatsapp_contact_url'] ?? '';

                if (isset($newPaths['logo'])) {
                    $settings->logo_path = $newPaths['logo'];
                }
                if (isset($newPaths['favicon'])) {
                    $settings->favicon_path = $newPaths['favicon'];
                }

                $settings->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_values($newPaths));
            throw $exception;
        }

        foreach ($previousPaths as $field => $path) {
            if ($path && isset($newPaths[$field]) && $newPaths[$field] !== $path && str_starts_with($path, 'general-settings/')) {
                Storage::disk('public')->delete($path);
            }
        }

        return back()->with('toast_success', 'General settings updated successfully.');
    }
}