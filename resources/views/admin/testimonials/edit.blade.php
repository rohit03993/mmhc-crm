<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('admin.partials.mobile-assets')
    <title>{{ $item ? 'Edit' : 'Add' }} testimonial - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 mmhc-admin-standalone">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        {{ $item ? 'Edit' : 'Add' }} testimonial
                    </h1>
                    <a href="{{ route('admin.testimonials.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
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

            @if($item && $item->image_path)
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <p class="text-gray-600 mb-4">Current photo:</p>
                    <img src="{{ storage_asset($item->image_path) }}" alt="" class="w-24 h-24 rounded-full object-cover border border-gray-200">
                </div>
            @endif

            <form action="{{ $item ? route('admin.testimonials.update', $item) : route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6 space-y-6">
                @csrf
                @if($item) @method('PUT') @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" required maxlength="255" placeholder="e.g. Sarah Johnson" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    <input type="file" name="image" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                    <p class="text-sm text-gray-500 mt-1">{{ $item ? 'Leave empty to keep current.' : 'Square or portrait works best.' }}</p>
                    @error('image') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quote / message <span class="text-red-500">*</span></label>
                    <textarea name="quote" rows="4" required maxlength="2000" placeholder="What the patient said about MMHC..." class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('quote', $item->quote ?? '') }}</textarea>
                    @error('quote') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating (0–5)</label>
                        <input type="number" name="rating" value="{{ old('rating', $item->rating ?? '5') }}" step="0.1" min="0" max="5" placeholder="5.0" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('rating') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Patient since</label>
                        <input type="text" name="patient_since" value="{{ old('patient_since', $item->patient_since ?? '') }}" maxlength="100" placeholder="e.g. Patient since 2022" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('patient_since') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                        <i class="fas fa-save mr-2"></i>{{ $item ? 'Save changes' : 'Add' }}
                    </button>
                    <a href="{{ route('admin.testimonials.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold text-gray-700">Cancel</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
