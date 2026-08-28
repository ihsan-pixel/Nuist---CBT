<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => AppSetting::query()->first() ?? new AppSetting,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_version' => ['required', 'string', 'max:50'],
            'foundation_name' => ['nullable', 'string', 'max:255'],
            'app_email' => ['nullable', 'email', 'max:255'],
            'app_description' => ['nullable', 'string'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $settings = AppSetting::query()->first() ?? new AppSetting;
        $settings->fill([
            'app_name' => $data['app_name'],
            'app_version' => $data['app_version'],
            'foundation_name' => $data['foundation_name'] ?? null,
            'app_email' => $data['app_email'] ?? null,
            'app_description' => $data['app_description'] ?? null,
        ]);

        if ($request->hasFile('app_logo')) {
            if ($settings->app_logo_path) {
                Storage::disk('public')->delete($settings->app_logo_path);
            }

            $settings->app_logo_path = $request->file('app_logo')->store('app', 'public');
        }

        $settings->save();

        return back()->with('status', 'Pengaturan aplikasi berhasil disimpan.');
    }
}
