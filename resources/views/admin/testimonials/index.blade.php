<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('admin.partials.mobile-assets')
    <title>What Our Patients Say - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 mmhc-admin-standalone">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-quote-left text-blue-600 mr-2"></i>
                        What Our Patients Say
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
                        <h3 class="font-semibold text-blue-900 mb-1">Landing page testimonials</h3>
                        <p class="text-blue-800 text-sm">These appear in the "What Our Patients Say" section. Edit name, photo, quote, rating, and "Patient since" text. Order below is the carousel sequence.</p>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <a href="{{ route('admin.testimonials.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    <i class="fas fa-plus mr-2"></i>Add testimonial
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <h2 class="text-lg font-bold text-gray-800 p-6 pb-0">Sequence (top = first in carousel)</h2>
                @forelse($items as $index => $item)
                    <div class="flex items-center gap-4 p-6 border-b border-gray-100 last:border-0">
                        <div class="flex flex-col gap-1">
                            <form action="{{ route('admin.testimonials.move-up', $item) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" @if($index === 0) disabled @endif class="p-2 rounded {{ $index === 0 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100' }}">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.testimonials.move-down', $item) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" @if($index === $items->count() - 1) disabled @endif class="p-2 rounded {{ $index === $items->count() - 1 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100' }}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </form>
                        </div>
                        @if($item->image_path)
                            <img src="{{ storage_asset($item->image_path) }}" alt="" class="w-16 h-16 rounded-full object-cover border border-gray-200">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                                <i class="fas fa-user text-2xl"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-800 font-semibold">{{ $item->name }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ Str::limit($item->quote, 60) }}</p>
                        </div>
                        <a href="{{ route('admin.testimonials.edit', $item) }}" class="px-3 py-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.testimonials.destroy', $item) }}" method="POST" onsubmit="return confirm('Remove this testimonial?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-500">
                        <i class="fas fa-quote-left text-4xl mb-3"></i>
                        <p>No testimonials yet. Add one above.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('landing') }}#testimonials" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                    <i class="fas fa-external-link-alt mr-2"></i>Preview section on landing page
                </a>
            </div>
        </main>
    </div>
</body>
</html>
