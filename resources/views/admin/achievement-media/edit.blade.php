<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit carousel image - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        Edit carousel image
                    </h1>
                    <a href="{{ route('admin.achievement-media.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to list
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

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <p class="text-gray-600 mb-4">Current image:</p>
                <img src="{{ asset('storage/' . $achievementMedia->image_path) }}" alt="" class="max-w-full h-auto max-h-48 rounded-lg border border-gray-200 mb-6">
            </div>

            <form action="{{ route('admin.achievement-media.update', $achievementMedia) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Replace image (optional)</label>
                    <input type="file" name="image" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                    <p class="text-sm text-gray-500 mt-1">Leave empty to keep the current image. Horizontal images work best.</p>
                    @error('image') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <input type="text" name="caption" value="{{ old('caption', $achievementMedia->caption) }}" maxlength="255" placeholder="e.g. International Excellence Award" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('caption') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                        <i class="fas fa-save mr-2"></i>Save changes
                    </button>
                    <a href="{{ route('admin.achievement-media.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold text-gray-700">Cancel</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
