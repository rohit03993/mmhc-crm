@extends('auth::layout')

@section('content')
<div class="container-fluid px-3 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.plans') }}" class="btn btn-link text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Plans
            </a>
            <h4 class="page-title mb-0 mt-2">Edit Plan: {{ $plan->name }}</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8">
            <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">Plan Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description *</label>
                            <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $plan->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price">Base Price (₹) *</label>
                                    <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="{{ old('price', $plan->price) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="monthly_price">Monthly Price (₹)</label>
                                    <input type="number" name="monthly_price" id="monthly_price" class="form-control" step="0.01" min="0" value="{{ old('monthly_price', $plan->monthly_price) }}">
                                    <small class="text-muted">Leave empty to use base price</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="members_included">Members Included</label>
                                    <input type="number" name="members_included" id="members_included" class="form-control" min="1" value="{{ old('members_included', $plan->members_included ?? 1) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="duration_days">Duration (Days) *</label>
                                    <input type="number" name="duration_days" id="duration_days" class="form-control" min="1" value="{{ old('duration_days', $plan->duration_days) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="currency">Currency *</label>
                            <select name="currency" id="currency" class="form-control" required>
                                <option value="INR" {{ old('currency', $plan->currency) == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                <option value="USD" {{ old('currency', $plan->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Landing Page Display</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="icon_class">Icon Class (FontAwesome)</label>
                                    <input type="text" name="icon_class" id="icon_class" class="form-control" placeholder="fa-heartbeat" value="{{ old('icon_class', $plan->icon_class) }}">
                                    <small class="text-muted">e.g., fa-heartbeat, fa-user-md, fa-users</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="color_theme">Color Theme</label>
                                    <select name="color_theme" id="color_theme" class="form-control">
                                        <option value="blue" {{ old('color_theme', $plan->color_theme ?? 'blue') == 'blue' ? 'selected' : '' }}>Blue</option>
                                        <option value="green" {{ old('color_theme', $plan->color_theme) == 'green' ? 'selected' : '' }}>Green</option>
                                        <option value="purple" {{ old('color_theme', $plan->color_theme) == 'purple' ? 'selected' : '' }}>Purple</option>
                                        <option value="orange" {{ old('color_theme', $plan->color_theme) == 'orange' ? 'selected' : '' }}>Orange</option>
                                        <option value="red" {{ old('color_theme', $plan->color_theme) == 'red' ? 'selected' : '' }}>Red</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="popular_label">Popular Label</label>
                            <input type="text" name="popular_label" id="popular_label" class="form-control" placeholder="Most Popular" value="{{ old('popular_label', $plan->popular_label) }}">
                            <small class="text-muted">Shown when plan is marked as popular</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="button_text">Button Text</label>
                                    <input type="text" name="button_text" id="button_text" class="form-control" value="{{ old('button_text', $plan->button_text ?? 'Get Started') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="button_link">Button Link (Optional)</label>
                                    <input type="text" name="button_link" id="button_link" class="form-control" value="{{ old('button_link', $plan->button_link) }}">
                                    <small class="text-muted">Leave empty for default registration link</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Features *</h5>
                    </div>
                    <div class="card-body">
                        <div id="features-list">
                            @foreach(old('features', $plan->features ?? []) as $index => $feature)
                            <div class="feature-item mb-2 d-flex gap-2">
                                <input type="text" name="features[]" class="form-control" value="{{ $feature }}" required>
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addFeature()">
                            <i class="fas fa-plus me-1"></i>Add Feature
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Active</label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_popular" id="is_popular" class="form-check-input" value="1" {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}>
                            <label for="is_popular" class="form-check-label">Mark as Popular</label>
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0" value="{{ old('sort_order', $plan->sort_order) }}">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Update Plan
                    </button>
                    <a href="{{ route('admin.plans') }}" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addFeature() {
    const featuresList = document.getElementById('features-list');
    const newFeature = document.createElement('div');
    newFeature.className = 'feature-item mb-2 d-flex gap-2';
    newFeature.innerHTML = `
        <input type="text" name="features[]" class="form-control" placeholder="Enter feature" required>
        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    featuresList.appendChild(newFeature);
}
</script>
@endsection

