<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $logoPath = SiteSetting::get('logo_path');
        $founderImagePath = SiteSetting::get('founder_image_path');
        $companyName = SiteSetting::get('company_name', 'MeD Miracle Health Care');
        $tagline = SiteSetting::get('tagline', 'Miracle Health Care');

        return view('admin.site-settings.index', compact('logoPath', 'founderImagePath', 'companyName', 'tagline'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'founder_image' => 'nullable|image|max:2048',
        ]);

        if (!empty($validated['company_name'])) {
            SiteSetting::set('company_name', $validated['company_name']);
        }
        if (array_key_exists('tagline', $validated)) {
            SiteSetting::set('tagline', $validated['tagline'] ?? '');
        }

        if ($request->hasFile('logo')) {
            $oldPath = SiteSetting::get('logo_path');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('site-settings', 'public');
            SiteSetting::set('logo_path', $path);
        }

        if ($request->hasFile('founder_image')) {
            $oldPath = SiteSetting::get('founder_image_path');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('founder_image')->store('site-settings', 'public');
            SiteSetting::set('founder_image_path', $path);
        }

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Site settings updated.');
    }
}
