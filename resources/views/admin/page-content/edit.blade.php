<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ ucfirst(str_replace('_', ' ', $section->section)) }} Section - MMHC Admin</title>
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
                        Edit {{ ucfirst(str_replace('_', ' ', $section->section)) }} Section
                    </h1>
                    <a href="{{ route('admin.page-content.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to All Sections
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form action="{{ route('admin.page-content.update', $section->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
                    
                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between pb-6 border-b">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Section Status</h3>
                            <p class="text-sm text-gray-600">Enable or disable this section on the landing page</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" class="sr-only peer" {{ $section->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900">{{ $section->is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heading text-blue-600 mr-2"></i>Section Title
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title', $section->title) }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('title')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subtitle -->
                    <div>
                        <label for="subtitle" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-align-left text-blue-600 mr-2"></i>Section Subtitle
                        </label>
                        <textarea id="subtitle" name="subtitle" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('subtitle', $section->subtitle) }}</textarea>
                        @error('subtitle')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dynamic Content Based on Section -->
                    @if($section->section === 'hero')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-mouse-pointer text-blue-600 mr-2"></i>Button Text
                            </label>
                            <input type="text" name="content[button_text]" value="{{ old('content.button_text', $section->content['button_text'] ?? 'Get Started Today') }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-3">
                            
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Secondary Button Text</label>
                            <input type="text" name="content[secondary_button_text]" value="{{ old('content.secondary_button_text', $section->content['secondary_button_text'] ?? 'Learn More') }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    @endif

                    @if($section->section === 'about')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-book text-blue-600 mr-2"></i>Our Story
                                </label>
                                <textarea name="content[story]" rows="4" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('content.story', $section->content['story'] ?? '') }}</textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-bullseye text-blue-600 mr-2"></i>Our Mission
                                </label>
                                <textarea name="content[mission]" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('content.mission', $section->content['mission'] ?? '') }}</textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-eye text-blue-600 mr-2"></i>Our Vision
                                </label>
                                <textarea name="content[vision]" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('content.vision', $section->content['vision'] ?? '') }}</textarea>
                            </div>
                        </div>
                    @endif

                    @if($section->section === 'contact')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Address
                                </label>
                                <textarea name="content[address]" rows="2" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('content.address', $section->content['address'] ?? '') }}</textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-phone text-blue-600 mr-2"></i>Phone
                                    </label>
                                    <input type="text" name="content[phone]" value="{{ old('content.phone', $section->content['phone'] ?? '') }}" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-ambulance text-blue-600 mr-2"></i>Emergency Phone
                                    </label>
                                    <input type="text" name="content[emergency_phone]" value="{{ old('content.emergency_phone', $section->content['emergency_phone'] ?? '') }}" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-envelope text-blue-600 mr-2"></i>Email
                                    </label>
                                    <input type="email" name="content[email]" value="{{ old('content.email', $section->content['email'] ?? '') }}" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-life-ring text-blue-600 mr-2"></i>Support Email
                                    </label>
                                    <input type="email" name="content[support_email]" value="{{ old('content.support_email', $section->content['support_email'] ?? '') }}" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($section->section === 'plans')
                        <!-- Healthcare Plans Management -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xl font-bold text-gray-800">
                                    <i class="fas fa-heartbeat text-red-600 mr-2"></i>
                                    Manage Healthcare Plans
                                </h3>
                                <a href="{{ route('admin.page-content.plans.create') }}" 
                                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                                    <i class="fas fa-plus mr-2"></i>Add New Plan
                                </a>
                            </div>
                            
                            @if(isset($healthcarePlans) && $healthcarePlans->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($healthcarePlans as $plan)
                                        <div class="bg-gray-50 rounded-lg border hover:shadow-md transition overflow-hidden {{ $plan->is_popular ? 'ring-2 ring-yellow-400' : '' }}">
                                            @if($plan->is_popular)
                                                <div class="bg-yellow-100 text-yellow-800 text-center py-2 px-4">
                                                    <strong class="text-sm">{{ $plan->popular_label ?: '⭐ Most Popular' }}</strong>
                                                </div>
                                            @endif
                                            
                                            <div class="p-4">
                                                <div class="flex items-center mb-3">
                                                    <div class="w-10 h-10 bg-{{ $plan->color_theme }}-100 rounded-full flex items-center justify-center mr-3">
                                                        <i class="{{ $plan->icon_class }} text-{{ $plan->color_theme }}-600"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-gray-800 text-sm">{{ $plan->name }}</h4>
                                                        <p class="text-gray-600 text-xs">{{ $plan->description }}</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <span class="text-xl font-bold text-{{ $plan->color_theme }}-600">{{ $plan->formatted_price }}</span>
                                                    <span class="text-gray-600 text-sm ml-1">{{ $plan->duration_text }}</span>
                                                </div>

                                                <div class="mb-3">
                                                    <h5 class="font-semibold text-gray-800 text-sm mb-1">Features:</h5>
                                                    <ul class="space-y-1">
                                                        @foreach(array_slice($plan->features, 0, 2) as $feature)
                                                            <li class="flex items-center text-xs text-gray-600">
                                                                <i class="fas fa-check text-green-500 mr-1"></i>{{ $feature }}
                                                            </li>
                                                        @endforeach
                                                        @if(count($plan->features) > 2)
                                                            <li class="text-gray-500 text-xs">+{{ count($plan->features) - 2 }} more...</li>
                                                        @endif
                                                    </ul>
                                                </div>

                                                <div class="flex justify-between items-center mb-3">
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    <span class="text-gray-500 text-xs">Order: {{ $plan->sort_order }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white px-4 py-3 border-t">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('admin.page-content.plans.edit', $plan) }}" 
                                                       class="flex-1 bg-blue-600 text-white text-center px-3 py-2 rounded-lg hover:bg-blue-700 transition text-xs font-semibold">
                                                        <i class="fas fa-edit mr-1"></i>Edit
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.page-content.plans.delete', $plan) }}" 
                                                          class="flex-1" onsubmit="return confirm('Are you sure you want to delete this plan?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="w-full bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition text-xs font-semibold">
                                                            <i class="fas fa-trash mr-1"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
                                    <i class="fas fa-heartbeat text-blue-400 text-4xl mb-4"></i>
                                    <h3 class="text-lg font-semibold text-blue-900 mb-2">No Healthcare Plans Found</h3>
                                    <p class="text-blue-800 mb-4">Create your first healthcare plan to get started.</p>
                                    <a href="{{ route('admin.page-content.plans.create') }}" 
                                       class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                                        <i class="fas fa-plus mr-2"></i>Create First Plan
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-image text-blue-600 mr-2"></i>Section Image (Optional)
                        </label>
                        
                        @if($section->image_path)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $section->image_path) }}" alt="Current Image" class="w-48 h-32 object-cover rounded-lg border">
                                <p class="text-sm text-gray-600 mt-1">Current image</p>
                            </div>
                        @endif
                        
                        <input type="file" name="image" accept="image/*" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-600 mt-1">Upload a new image to replace the current one (Max 5MB)</p>
                        @error('image')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Save Button -->
                    <div class="flex items-center justify-between pt-6 border-t">
                        <a href="{{ route('admin.page-content.index') }}" class="text-gray-600 hover:text-gray-800 font-semibold">
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

