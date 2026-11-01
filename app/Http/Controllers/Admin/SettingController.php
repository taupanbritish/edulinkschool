<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public const KEYS = [
        'school_name', 'school_short', 'tagline', 'hero_title', 'hero_subtitle', 'npsn', 'accreditation', 'founded_year',
        'address', 'phone', 'whatsapp', 'email', 'office_hours', 'maps_url',
        'social_instagram', 'social_youtube', 'social_facebook', 'social_tiktok',
        'principal_name', 'principal_welcome', 'vision', 'mission', 'history', 'youtube_url',
        'curriculum_name', 'curriculum_description',
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'values' => Setting::query()->pluck('value', 'key')->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'school_short' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'hero_title' => ['nullable', 'string', 'max:150'],
            'hero_subtitle' => ['nullable', 'string', 'max:400'],
            'npsn' => ['nullable', 'string', 'max:30'],
            'accreditation' => ['nullable', 'string', 'max:10'],
            'founded_year' => ['nullable', 'digits:4'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'office_hours' => ['nullable', 'string', 'max:100'],
            'maps_url' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_tiktok' => ['nullable', 'url', 'max:255'],
            'principal_name' => ['nullable', 'string', 'max:150'],
            'principal_welcome' => ['nullable', 'string', 'max:2000'],
            'vision' => ['nullable', 'string', 'max:500'],
            'mission' => ['nullable', 'string', 'max:2000'],
            'history' => ['nullable', 'string', 'max:2000'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'curriculum_name' => ['nullable', 'string', 'max:100'],
            'curriculum_description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,jpg,jpeg,webp', 'max:1024'],
            'principal_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $logo = Photo::update(
            $request->file('logo'),
            Setting::get('logo_path'),
            $request->boolean('remove_logo'),
            'photos/branding'
        );
        Setting::set('logo_path', $logo);

        $favicon = Photo::update(
            $request->file('favicon'),
            Setting::get('favicon_path'),
            $request->boolean('remove_favicon'),
            'photos/branding'
        );
        Setting::set('favicon_path', $favicon);

        $principalPhoto = Photo::update(
            $request->file('principal_photo'),
            Setting::get('principal_photo_path'),
            $request->boolean('remove_principal_photo'),
            'photos/principal'
        );
        Setting::set('principal_photo_path', $principalPhoto);

        foreach (self::KEYS as $key) {
            Setting::set($key, $data[$key] ?? null);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}
