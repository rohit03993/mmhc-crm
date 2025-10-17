<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Healthcare Plans - MMHC Admin</title>
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
                        <i class="fas fa-heartbeat text-blue-600 mr-2"></i>
                        Manage Healthcare Plans
                    </h1>
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.page-content.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Page Content
                        </a>
                        <a href="{{ route('admin.healthcare-plans.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-plus mr-2"></i>Add New Plan
                        </a>
                    </div>
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
                        <h3 class="font-semibold text-blue-900 mb-1">Manage Healthcare Plans</h3>
                        <p class="text-blue-800 text-sm">Edit the healthcare plans shown on your landing page. Changes will be reflected immediately.</p>
                    </div>
                </div>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($plans as $plan)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6">
                        <!-- Plan Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-{{ $plan->color_theme }}-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas {{ $plan->icon_class }} text-{{ $plan->color_theme }}-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{ $plan->name }}</h3>
                                    <div class="text-2xl font-bold text-{{ $plan->color_theme }}-600">₹{{ number_format($plan->price) }}</div>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Popular Badge -->
                        @if($plan->is_popular)
                            <div class="mb-4">
                                <span class="bg-{{ $plan->color_theme }}-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $plan->popular_label }}
                                </span>
                            </div>
                        @endif

                        <!-- Description -->
                        <p class="text-gray-600 text-sm mb-4">{{ $plan->description }}</p>

                        <!-- Features Count -->
                        <div class="mb-4">
                            <span class="text-sm text-gray-500">{{ count($plan->features) }} features included</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.healthcare-plans.edit', $plan) }}" 
                               class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-sm font-semibold hover:bg-blue-700 transition text-center">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form action="{{ route('admin.healthcare-plans.destroy', $plan) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-600 text-white px-3 py-2 rounded text-sm font-semibold hover:bg-red-700 transition"
                                        onclick="return confirm('Are you sure you want to delete this plan?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-4 text-center py-12">
                        <i class="fas fa-heartbeat text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-600">No healthcare plans found.</p>
                        <a href="{{ route('admin.healthcare-plans.create') }}" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            Create First Plan
                        </a>
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
