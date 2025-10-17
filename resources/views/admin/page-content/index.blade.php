<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Landing Page Content - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        Manage Landing Page Content
                    </h1>
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-1">Edit Landing Page Sections</h3>
                        <p class="text-blue-800 text-sm">Click on any section below to edit its content. Changes will be reflected on the landing page immediately.</p>
                    </div>
                </div>
            </div>

            <!-- Sections Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($sections as $section)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-800 capitalize">
                                {{ str_replace('_', ' ', $section->section) }}
                            </h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $section->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $section->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        
                        @if($section->title)
                            <p class="text-gray-600 mb-4 line-clamp-2">{{ $section->title }}</p>
                        @endif
                        
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <i class="fas fa-clock mr-2"></i>
                            Updated {{ $section->updated_at->diffForHumans() }}
                        </div>
                        
                        <a href="{{ route('admin.page-content.edit', $section->id) }}" 
                           class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                            <i class="fas fa-edit mr-2"></i>Edit Section
                        </a>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-600">No page sections found. Run the seeder to create initial content.</p>
                        <p class="text-sm text-gray-500 mt-2">Run: <code class="bg-gray-100 px-2 py-1 rounded">php artisan db:seed --class=PageContentSeeder</code></p>
                    </div>
                @endforelse
            </div>


            <!-- Preview Link -->
            <div class="mt-8 text-center">
                <a href="{{ route('landing') }}" target="_blank" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Preview Landing Page
                </a>
            </div>
        </main>
    </div>
</body>
</html>

