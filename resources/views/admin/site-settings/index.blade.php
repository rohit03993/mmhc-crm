<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
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
                        <p class="text-blue-800 text-sm">These details appear in the sidebar, login/register pages, and landing page header. Leave logo empty to keep the current image.</p>
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
                            <img src="{{ Storage::url($logoPath) }}" alt="Current logo" class="h-14 w-auto border border-gray-200 rounded">
                            <span class="text-sm text-gray-500">Current logo. Upload a new image to replace.</span>
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                    @error('logo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
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

                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    <i class="fas fa-save mr-2"></i>Save settings
                </button>
            </form>
        </main>
    </div>
</body>
</html>
