<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Healthcare Plan - MMHC Admin</title>
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
                        <i class="fas fa-plus text-green-600 mr-2"></i>
                        Create New Healthcare Plan
                    </h1>
                    <a href="{{ route('admin.page-content.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Page Content
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.page-content.plans.store') }}" class="space-y-6">
                @csrf
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Basic Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Plan Name *</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="e.g., Basic Plan" required>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <input type="text" id="description" name="description" value="{{ old('description') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="e.g., Essential healthcare services">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                            <input type="number" id="price" name="price" value="{{ old('price') }}" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="500.00" required>
                        </div>
                        
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Currency *</label>
                            <select id="currency" name="currency" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="₹" {{ old('currency') == '₹' ? 'selected' : '' }}>₹ (Rupee)</option>
                                <option value="$" {{ old('currency') == '$' ? 'selected' : '' }}>$ (Dollar)</option>
                                <option value="€" {{ old('currency') == '€' ? 'selected' : '' }}>€ (Euro)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="duration_text" class="block text-sm font-medium text-gray-700 mb-2">Duration Text *</label>
                            <input type="text" id="duration_text" name="duration_text" value="{{ old('duration_text', '/month') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="/month" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="icon_class" class="block text-sm font-medium text-gray-700 mb-2">Icon Class</label>
                            <input type="text" id="icon_class" name="icon_class" value="{{ old('icon_class') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="e.g., fa-heartbeat">
                            <p class="text-sm text-gray-500 mt-1">Font Awesome icon class (e.g., fa-heartbeat, fa-stethoscope)</p>
                        </div>
                        
                        <div>
                            <label for="color_theme" class="block text-sm font-medium text-gray-700 mb-2">Color Theme *</label>
                            <select id="color_theme" name="color_theme" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="blue" {{ old('color_theme') == 'blue' ? 'selected' : '' }}>Blue</option>
                                <option value="green" {{ old('color_theme') == 'green' ? 'selected' : '' }}>Green</option>
                                <option value="purple" {{ old('color_theme') == 'purple' ? 'selected' : '' }}>Purple</option>
                                <option value="orange" {{ old('color_theme') == 'orange' ? 'selected' : '' }}>Orange</option>
                                <option value="red" {{ old('color_theme') == 'red' ? 'selected' : '' }}>Red</option>
                                <option value="indigo" {{ old('color_theme') == 'indigo' ? 'selected' : '' }}>Indigo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="button_text" class="block text-sm font-medium text-gray-700 mb-2">Button Text *</label>
                            <input type="text" id="button_text" name="button_text" value="{{ old('button_text', 'Get Started') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Get Started" required>
                        </div>
                        
                        <div>
                            <label for="button_link" class="block text-sm font-medium text-gray-700 mb-2">Button Link</label>
                            <input type="url" id="button_link" name="button_link" value="{{ old('button_link') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="https://example.com (optional)">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Features</h2>
                    
                    <div id="features-container">
                        <div class="feature-input flex items-center gap-3 mb-3">
                            <input type="text" name="features[]" value="{{ old('features.0') }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Enter a feature">
                            <button type="button" onclick="removeFeature(this)" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="button" onclick="addFeature()" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Add Feature
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Settings</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0">
                            <p class="text-sm text-gray-500 mt-1">Lower numbers appear first</p>
                        </div>
                        
                        <div>
                            <label for="popular_label" class="block text-sm font-medium text-gray-700 mb-2">Popular Label</label>
                            <input type="text" id="popular_label" name="popular_label" value="{{ old('popular_label') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="e.g., Most Popular, Family Choice">
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_popular" value="1" {{ old('is_popular') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Mark as Popular Plan</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.page-content.index') }}" 
                       class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition font-semibold">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Create Plan
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        function addFeature() {
            const container = document.getElementById('features-container');
            const featureDiv = document.createElement('div');
            featureDiv.className = 'feature-input flex items-center gap-3 mb-3';
            featureDiv.innerHTML = `
                <input type="text" name="features[]" 
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="Enter a feature">
                <button type="button" onclick="removeFeature(this)" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(featureDiv);
        }

        function removeFeature(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>
