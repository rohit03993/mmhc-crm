<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $healthcarePlan->name }} - MMHC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        Edit {{ $healthcarePlan->name }}
                    </h1>
                    <a href="{{ route('admin.healthcare-plans.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to All Plans
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form action="{{ route('admin.healthcare-plans.update', $healthcarePlan) }}" method="POST" x-data="{ featureCount: {{ count($healthcarePlan->features) }} }">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
                    
                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between pb-6 border-b">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Plan Status</h3>
                            <p class="text-sm text-gray-600">Enable or disable this plan on the landing page</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" class="sr-only peer" {{ $healthcarePlan->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900">{{ $healthcarePlan->is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>

                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tag text-blue-600 mr-2"></i>Plan Name
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $healthcarePlan->name) }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-sort text-blue-600 mr-2"></i>Sort Order
                            </label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $healthcarePlan->sort_order) }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-align-left text-blue-600 mr-2"></i>Plan Description
                        </label>
                        <textarea id="description" name="description" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $healthcarePlan->description) }}</textarea>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-rupee-sign text-blue-600 mr-2"></i>Price (₹)
                            </label>
                            <input type="number" id="price" name="price" step="0.01" value="{{ old('price', $healthcarePlan->price) }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-coins text-blue-600 mr-2"></i>Currency
                            </label>
                            <select id="currency" name="currency" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="INR" {{ $healthcarePlan->currency === 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                <option value="USD" {{ $healthcarePlan->currency === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ $healthcarePlan->currency === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            </select>
                        </div>

                        <div>
                            <label for="duration_days" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar text-blue-600 mr-2"></i>Duration (Days)
                            </label>
                            <input type="number" id="duration_days" name="duration_days" value="{{ old('duration_days', $healthcarePlan->duration_days) }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">30 = Monthly, 365 = Yearly</p>
                        </div>
                    </div>

                    <!-- Design Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="icon_class" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-palette text-blue-600 mr-2"></i>Icon Class
                            </label>
                            <input type="text" id="icon_class" name="icon_class" value="{{ old('icon_class', $healthcarePlan->icon_class) }}" 
                                   placeholder="fa-heartbeat, fa-stethoscope, fa-crown, fa-users"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">FontAwesome icon class (e.g., fa-heartbeat)</p>
                        </div>

                        <div>
                            <label for="color_theme" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-paint-brush text-blue-600 mr-2"></i>Color Theme
                            </label>
                            <select id="color_theme" name="color_theme" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="blue" {{ $healthcarePlan->color_theme === 'blue' ? 'selected' : '' }}>Blue</option>
                                <option value="green" {{ $healthcarePlan->color_theme === 'green' ? 'selected' : '' }}>Green</option>
                                <option value="purple" {{ $healthcarePlan->color_theme === 'purple' ? 'selected' : '' }}>Purple</option>
                                <option value="orange" {{ $healthcarePlan->color_theme === 'orange' ? 'selected' : '' }}>Orange</option>
                                <option value="red" {{ $healthcarePlan->color_theme === 'red' ? 'selected' : '' }}>Red</option>
                                <option value="yellow" {{ $healthcarePlan->color_theme === 'yellow' ? 'selected' : '' }}>Yellow</option>
                            </select>
                        </div>
                    </div>

                    <!-- Popular Settings -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Popular Plan Settings</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center">
                                <input type="checkbox" id="is_popular" name="is_popular" value="1" {{ $healthcarePlan->is_popular ? 'checked' : '' }} 
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="is_popular" class="ml-2 text-sm font-medium text-gray-700">Mark as Popular Plan</label>
                            </div>

                            <div>
                                <label for="popular_label" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Popular Badge Text
                                </label>
                                <input type="text" id="popular_label" name="popular_label" value="{{ old('popular_label', $healthcarePlan->popular_label) }}" 
                                       placeholder="Most Popular, Family Choice, etc."
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Plan Features</h3>
                        
                        <div x-data="{ features: @js($healthcarePlan->features) }">
                            <template x-for="(feature, index) in features" :key="index">
                                <div class="flex items-center mb-3">
                                    <input type="text" :name="`features[${index}]`" :value="feature" 
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="Enter feature">
                                    <button type="button" @click="features.splice(index, 1)" 
                                            class="ml-2 text-red-600 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </template>
                            
                            <button type="button" @click="features.push('')" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-plus mr-2"></i>Add Feature
                            </button>
                        </div>
                    </div>

                    <!-- Button Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="button_text" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-mouse-pointer text-blue-600 mr-2"></i>Button Text
                            </label>
                            <input type="text" id="button_text" name="button_text" value="{{ old('button_text', $healthcarePlan->button_text) }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="button_link" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-link text-blue-600 mr-2"></i>Custom Button Link (Optional)
                            </label>
                            <input type="url" id="button_link" name="button_link" value="{{ old('button_link', $healthcarePlan->button_link) }}" 
                                   placeholder="Leave empty for default registration"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex items-center justify-between pt-6 border-t">
                        <a href="{{ route('admin.healthcare-plans.index') }}" class="text-gray-600 hover:text-gray-800 font-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <!-- Preview -->
            <div class="mt-6 text-center">
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
