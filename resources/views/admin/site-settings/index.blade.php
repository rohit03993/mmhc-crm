<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('admin.partials.mobile-assets')
    <title>Site Settings - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 mmhc-admin-standalone">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-cog text-blue-600 mr-2"></i>
                        Site Settings
                    </h1>
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-1">Logo & branding</h3>
                        <p class="text-blue-800 text-sm">These details appear in the sidebar, login/register pages, and landing page header. The PWA / App icon controls what users see when they install the site on their phone. Leave uploads empty to keep the current images.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    @if($logoPath)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ storage_asset($logoPath) }}" alt="Current logo" class="h-14 w-auto border border-gray-200 rounded">
                            <span class="text-sm text-gray-500">Current logo. Upload a new image to replace.</span>
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                    @error('logo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Founder image (Meet Our Founder section)</label>
                    @if($founderImagePath ?? null)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ storage_asset($founderImagePath) }}" alt="Founder" class="h-24 w-24 rounded-full object-cover border border-gray-200">
                            <span class="text-sm text-gray-500">Current founder photo. Upload a new image to replace.</span>
                        </div>
                    @endif
                    <input type="file" name="founder_image" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                    @error('founder_image') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PWA / App icon (Add to Home Screen)</label>
                    <div class="mb-3 flex items-center gap-4">
                        <img src="{{ $pwaIconPreviewUrl }}" alt="Current PWA icon" class="h-16 w-16 rounded-xl object-cover border border-gray-200 bg-gray-100">
                        <div class="text-sm text-gray-500">
                            <p>Shown when users install the app on their phone.</p>
                            <p class="mt-1">Use a square PNG (512×512 recommended). Brand logo with blue background works best.</p>
                        </div>
                    </div>
                    <input type="file" name="pwa_icon" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                    @error('pwa_icon') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" maxlength="255" placeholder="e.g. MeD Miracle Health Care" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('company_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tagline (below logo in sidebar)</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $tagline) }}" maxlength="255" placeholder="e.g. Miracle Health Care" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('tagline') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <hr class="my-6 border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-address-book text-blue-600 mr-2"></i>Contact Information (Contact page & footer)
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Corporate Office Address</label>
                    <textarea name="contact_address" rows="3" placeholder="Full address (line breaks allowed)" class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('contact_address', $contactAddress ?? '') }}</textarea>
                    @error('contact_address') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone (24×7)</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $contactPhone ?? '') }}" maxlength="100" placeholder="e.g. 9113311256" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('contact_phone') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Website (shown under phone)</label>
                    <input type="text" name="contact_website" value="{{ old('contact_website', $contactWebsite ?? '') }}" maxlength="255" placeholder="e.g. www.themmhc.com" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('contact_website') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $contactEmail ?? '') }}" maxlength="255" placeholder="e.g. Care@themmhc.com" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('contact_email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Locations</label>
                    <textarea name="service_locations" rows="2" placeholder="e.g. Patna | Ranchi | Bhopal (line breaks allowed)" class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('service_locations', $serviceLocations ?? '') }}</textarea>
                    @error('service_locations') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    <i class="fas fa-save mr-2"></i>Save settings
                </button>
            </form>
        </main>
    </div>
</body>
</html>
