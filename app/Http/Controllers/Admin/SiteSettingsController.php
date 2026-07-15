<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\PwaIconService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $logoPath = SiteSetting::get('logo_path');
        $founderImagePath = SiteSetting::get('founder_image_path');
        $pwaIconPath = SiteSetting::get('pwa_icon_path');
        $companyName = SiteSetting::get('company_name', 'MeD Miracle Health Care');
        $tagline = SiteSetting::get('tagline', 'Miracle Health Care');
        $contactAddress = SiteSetting::get('contact_address', "Udgam Incubation Centre, Rohit Nagar\nPhase 1 (Near Surya Children School)\nBhopal 462023, Madhya Pradesh");
        $contactPhone = SiteSetting::get('contact_phone', '9113311256');
        $contactWebsite = SiteSetting::get('contact_website', 'www.themmhc.com');
        $contactEmail = SiteSetting::get('contact_email', 'Care@themmhc.com');
        $serviceLocations = SiteSetting::get('service_locations', "Patna | Ranchi | Bhopal\nNoida | Gurgaon");
        $pwaIconPreviewUrl = app(PwaIconService::class)->iconUrl(192);

        return view('admin.site-settings.index', compact(
            'logoPath', 'founderImagePath', 'pwaIconPath', 'pwaIconPreviewUrl',
            'companyName', 'tagline',
            'contactAddress', 'contactPhone', 'contactWebsite', 'contactEmail', 'serviceLocations'
        ));
    }

    public function update(Request $request, PwaIconService $pwaIcons)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'founder_image' => 'nullable|image|max:2048',
            'pwa_icon' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'contact_address' => 'nullable|string|max:1000',
            'contact_phone' => 'nullable|string|max:100',
            'contact_website' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'service_locations' => 'nullable|string|max:500',
        ]);

        if (!empty($validated['company_name'])) {
            SiteSetting::set('company_name', $validated['company_name']);
        }
        if (array_key_exists('tagline', $validated)) {
            SiteSetting::set('tagline', $validated['tagline'] ?? '');
        }
        if (array_key_exists('contact_address', $validated)) {
            SiteSetting::set('contact_address', $validated['contact_address'] ?? '');
        }
        if (array_key_exists('contact_phone', $validated)) {
            SiteSetting::set('contact_phone', $validated['contact_phone'] ?? '');
        }
        if (array_key_exists('contact_website', $validated)) {
            SiteSetting::set('contact_website', $validated['contact_website'] ?? '');
        }
        if (array_key_exists('contact_email', $validated)) {
            SiteSetting::set('contact_email', $validated['contact_email'] ?? '');
        }
        if (array_key_exists('service_locations', $validated)) {
            SiteSetting::set('service_locations', $validated['service_locations'] ?? '');
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

        if ($request->hasFile('pwa_icon')) {
            $pwaIcons->storeUploadedIcon($request->file('pwa_icon'));
        }

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Site settings updated.');
    }
}
