<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements & Media Coverage - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-trophy text-blue-600 mr-2"></i>
                        Achievements & Media Coverage
                    </h1>
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-1">Landing page carousel</h3>
                        <p class="text-blue-800 text-sm">Images appear in the "Achievements & Media Coverage" section above Core Values, in a horizontal carousel. Order below is the display sequence. Use Move up / Move down to change order.</p>
                    </div>
                </div>
            </div>

            <!-- Add image form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Add image</h2>
                <form action="{{ route('admin.achievement-media.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image (horizontal recommended)</label>
                        <input type="file" name="image" accept="image/*" required class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                        @error('image') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Caption (optional)</label>
                        <input type="text" name="caption" value="{{ old('caption') }}" maxlength="255" placeholder="e.g. Award 2024" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('caption') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                        <i class="fas fa-plus mr-2"></i>Add
                    </button>
                </form>
            </div>

            <!-- List in sequence -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <h2 class="text-lg font-bold text-gray-800 p-6 pb-0">Sequence (top = first in carousel)</h2>
                @forelse($items as $index => $item)
                    <div class="flex items-center gap-4 p-6 border-b border-gray-100 last:border-0">
                        <div class="flex flex-col gap-1">
                            <form action="{{ route('admin.achievement-media.move-up', $item) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" @if($index === 0) disabled @endif class="p-2 rounded {{ $index === 0 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100' }}">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.achievement-media.move-down', $item) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" @if($index === $items->count() - 1) disabled @endif class="p-2 rounded {{ $index === $items->count() - 1 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100' }}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </form>
                        </div>
                        <img src="{{ asset('achievement-media/' . basename($item->image_path)) }}" alt="" class="w-24 h-16 object-cover rounded-lg border border-gray-200">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-800 font-medium truncate">{{ $item->caption ?: '—' }}</p>
                            <p class="text-sm text-gray-500">#{{ $index + 1 }}</p>
                        </div>
                        <a href="{{ route('admin.achievement-media.edit', $item) }}" class="px-3 py-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit image and caption">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.achievement-media.destroy', $item) }}" method="POST" onsubmit="return confirm('Remove this image?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-500">
                        <i class="fas fa-images text-4xl mb-3"></i>
                        <p>No images yet. Add one above.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('landing') }}#achievement-media" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                    <i class="fas fa-external-link-alt mr-2"></i>Preview section on landing page
                </a>
            </div>
        </main>
    </div>
</body>
</html>
