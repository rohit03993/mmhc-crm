@extends('auth::layout')

@section('title', 'My Documents - MMHC CRM')

@section('content')
<!-- Mobile App View for Documents -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('profile.index') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">My Documents</div>
                    <div class="app-header-subtitle">{{ $documents->total() }} {{ Str::plural('document', $documents->total()) }}</div>
                </div>
            </div>
            <div class="app-header-right">
                <button class="app-header-icon" onclick="document.getElementById('uploadForm').scrollIntoView({behavior: 'smooth'})">
                    <i class="fas fa-plus-circle"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <h2 class="text-primary">My Documents</h2>
            <p class="text-muted">Manage your uploaded documents</p>
        </div>

        <!-- Document Categories Summary - For Staff (Nurses/Caregivers) -->
        @if(auth()->user()->isStaff())
        <div class="document-categories-section mb-4">
            <div class="document-categories-header mb-3">
                <h4 class="document-categories-title">
                    <i class="fas fa-folder-open me-2"></i>Document Categories
                </h4>
            </div>
            <div class="row g-3">
                @php
                    $categories = [
                        'certificate' => ['icon' => 'fa-certificate', 'color' => 'purple', 'label' => 'Certificates'],
                        'id_proof' => ['icon' => 'fa-id-card', 'color' => 'green', 'label' => 'ID Proofs'],
                        'medical_license' => ['icon' => 'fa-user-md', 'color' => 'blue', 'label' => 'Medical License'],
                        'insurance' => ['icon' => 'fa-shield-alt', 'color' => 'orange', 'label' => 'Insurance'],
                    ];
                @endphp
                @foreach($categories as $type => $category)
                    @php
                        $count = $categoryCounts[$type] ?? 0;
                    @endphp
                    <div class="col-6 col-md-3">
                        <div class="document-category-card category-{{ $category['color'] }}">
                            <div class="document-category-icon">
                                <i class="fas {{ $category['icon'] }}"></i>
                            </div>
                            <div class="document-category-content">
                                <div class="document-category-count">{{ $count }}</div>
                                <div class="document-category-label">{{ $category['label'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Upload Document Card - Enhanced -->
        <div class="upload-document-card mb-4" id="uploadForm">
            <div class="upload-card-header">
                <div class="upload-card-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div>
                    <h3 class="upload-card-title">Upload New Document</h3>
                    <p class="upload-card-subtitle">Add your professional documents</p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data" class="upload-form">
                @csrf
                
                <div class="app-form-group">
                    <label for="document_type" class="app-form-label">
                        <i class="fas fa-tag me-2"></i>Document Type
                    </label>
                    <select class="app-form-select @error('document_type') is-invalid @enderror" 
                            id="document_type" 
                            name="document_type" 
                            required>
                        <option value="">Select Document Type</option>
                        @foreach($allowedDocumentTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('document_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('document_type')
                        <div class="app-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="app-form-group">
                    <label for="document_name" class="app-form-label">
                        <i class="fas fa-file-signature me-2"></i>Document Name
                    </label>
                    <input type="text" 
                           class="app-form-input @error('document_name') is-invalid @enderror" 
                           id="document_name" 
                           name="document_name" 
                           value="{{ old('document_name') }}" 
                           placeholder="{{ auth()->user()->isStaff() ? 'e.g., Nursing Certificate' : 'e.g., Blood Test Report' }}"
                           required>
                    @error('document_name')
                        <div class="app-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="app-form-group">
                    <label for="document_file" class="app-form-label">
                        <i class="fas fa-paperclip me-2"></i>Choose File
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" 
                               class="file-upload-input @error('document_file') is-invalid @enderror" 
                               id="document_file" 
                               name="document_file" 
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               onchange="updateFileName(this)"
                               required>
                        <label for="document_file" class="file-upload-label">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">
                                <span class="file-upload-main">Tap to select file</span>
                                <span class="file-upload-hint">Max 10MB - PDF, JPG, PNG, DOC, DOCX</span>
                            </div>
                            <div class="file-name-display" id="fileNameDisplay" style="display: none;">
                                <i class="fas fa-file me-1"></i>
                                <span id="fileName"></span>
                            </div>
                        </label>
                    </div>
                    @error('document_file')
                        <div class="app-form-error">{{ $message }}</div>
                    @enderror
                </div>

                @if($errors->any())
                    <div class="app-alert app-alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="app-form-submit">
                    <button type="submit" class="app-btn-submit">
                        <i class="fas fa-upload me-2"></i>Upload Document
                    </button>
                </div>
            </form>
        </div>

        <!-- Documents List - Enhanced -->
        <div class="documents-list-section">
            <div class="documents-list-header">
                <h4 class="documents-list-title">
                    <i class="fas fa-file-alt me-2"></i>My Documents
                </h4>
                @if($documents->count() > 0)
                    <a href="{{ route('documents.index') }}" class="documents-view-all">
                        View All →
                    </a>
                @endif
            </div>
            
            @if($documents->count() > 0)
                <div class="documents-list-cards">
                    @foreach($documents as $document)
                    <div class="document-card-modern">
                        <div class="document-card-header">
                            <div class="document-card-icon-wrapper">
                                <div class="document-card-icon document-icon-{{ strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION)) }}">
                                    <i class="{{ $document->file_icon }}"></i>
                                </div>
                            </div>
                            <div class="document-card-info">
                                <h5 class="document-card-name">{{ $document->document_name }}</h5>
                                <div class="document-card-meta">
                                    <span class="document-badge document-badge-type">{{ $document->document_type_display }}</span>
                                    <span class="document-badge document-badge-{{ $document->status == 'verified' ? 'success' : ($document->status == 'rejected' ? 'danger' : 'warning') }}">
                                        {{ $document->status_display }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="document-card-body">
                            <div class="document-card-details">
                                <div class="document-detail-item">
                                    <i class="fas fa-file me-2"></i>
                                    <span>{{ $document->file_size }}</span>
                                </div>
                                <div class="document-detail-item">
                                    <i class="fas fa-calendar me-2"></i>
                                    <span>{{ $document->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            
                            <div class="document-card-actions">
                                @php
                                    $isViewable = in_array($document->mime_type, ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
                                @endphp
                                @if($isViewable)
                                <a href="{{ route('documents.view', $document->id) }}" 
                                   class="document-action-btn document-action-view" 
                                   target="_blank"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                    <span>View</span>
                                </a>
                                @endif
                                <a href="{{ route('documents.download', $document->id) }}" 
                                   class="document-action-btn document-action-download"
                                   title="Download">
                                    <i class="fas fa-download"></i>
                                    <span>Download</span>
                                </a>
                                <button class="document-action-btn document-action-delete" 
                                        onclick="deleteDocument({{ $document->id }})"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($documents->hasPages())
                    <div class="documents-pagination">
                        {{ $documents->links() }}
                    </div>
                @endif
            @else
                <div class="documents-empty-state">
                    <div class="documents-empty-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h4 class="documents-empty-title">No Documents Yet</h4>
                    <p class="documents-empty-text">Upload your first document using the form above</p>
                    <button class="btn btn-primary mt-3" onclick="document.getElementById('uploadForm').scrollIntoView({behavior: 'smooth'})">
                        <i class="fas fa-upload me-2"></i>Upload Document
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Navigation -->
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Delete Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this document? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile App Container */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 140px !important;
    margin-top: 0;
}

@media (max-width: 767px) {
    .mobile-app-container {
        padding-bottom: 160px !important;
    }
}

/* App Header Mobile */
.app-header-mobile {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 12px 16px;
    padding-top: max(12px, env(safe-area-inset-top));
}

.app-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.app-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-back-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
    transition: all 0.2s ease;
}

.app-back-btn:active {
    background: rgba(255,255,255,0.3);
    transform: scale(0.95);
}

.app-header-title {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.2;
    color: #ffffff !important;
}

.app-header-subtitle {
    font-size: 0.8rem;
    opacity: 0.9;
    color: #ffffff !important;
}

.app-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.app-header-icon:active {
    background: rgba(255,255,255,0.3);
    transform: scale(0.95);
}

/* App Content */
.app-content {
    padding: 16px;
    padding-bottom: 20px;
}

@media (max-width: 767px) {
    .app-content {
        padding-bottom: 40px;
    }
}

/* Document Categories Section - For Staff */
.document-categories-section {
    margin-bottom: 1.5rem;
}

.document-categories-header {
    padding: 0 0.5rem;
}

.document-categories-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #212529 !important;
    margin: 0;
    display: flex;
    align-items: center;
}

.document-categories-title i {
    color: #667eea !important;
}

.document-category-card {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.document-category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.document-category-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    flex-shrink: 0;
}

.category-purple .document-category-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.category-green .document-category-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.category-blue .document-category-icon {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.category-orange .document-category-icon {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.document-category-content {
    flex: 1;
}

.document-category-count {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529 !important;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.document-category-label {
    font-size: 0.85rem;
    color: #6c757d !important;
    font-weight: 600;
}

/* Upload Document Card */
.upload-document-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.upload-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.upload-card-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    flex-shrink: 0;
}

.upload-card-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #212529 !important;
    margin: 0 0 0.25rem 0;
}

.upload-card-subtitle {
    font-size: 0.9rem;
    color: #6c757d !important;
    margin: 0;
}

/* File Upload Wrapper */
.file-upload-wrapper {
    position: relative;
}

.file-upload-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.5rem;
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 150px;
}

.file-upload-label:hover {
    border-color: #667eea;
    background: #f0f4ff;
}

.file-upload-label:active {
    transform: scale(0.98);
}

.file-upload-icon {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.file-upload-text {
    text-align: center;
}

.file-upload-main {
    display: block;
    font-size: 1rem;
    font-weight: 600;
    color: #212529 !important;
    margin-bottom: 0.5rem;
}

.file-upload-hint {
    display: block;
    font-size: 0.85rem;
    color: #6c757d !important;
}

.file-name-display {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: #e7f3ff;
    border-radius: 8px;
    color: #0066cc !important;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Form Styles */
.app-form-group {
    margin-bottom: 1.25rem;
}

.app-form-label {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    font-weight: 600;
    color: #495057 !important;
    margin-bottom: 0.5rem;
}

.app-form-label i {
    color: #667eea !important;
}

.app-form-input,
.app-form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    color: #212529 !important;
    background: white;
    transition: all 0.2s ease;
}

.app-form-input:focus,
.app-form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.app-form-error {
    font-size: 0.8rem;
    color: #dc2626 !important;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.app-form-submit {
    margin-top: 1.5rem;
}

.app-btn-submit {
    width: 100%;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    position: relative;
    z-index: 100;
}

.app-btn-submit:hover,
.app-btn-submit:active {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Documents List Section */
.documents-list-section {
    margin-top: 1.5rem;
}

.documents-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0 0.5rem;
}

.documents-list-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #212529 !important;
    margin: 0;
    display: flex;
    align-items: center;
}

.documents-list-title i {
    color: #667eea !important;
}

.documents-view-all {
    font-size: 0.9rem;
    color: #667eea !important;
    text-decoration: none;
    font-weight: 600;
}

/* Document Cards */
.documents-list-cards {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.document-card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.document-card-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    border-color: #667eea;
}

.document-card-header {
    padding: 1.25rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.document-card-icon-wrapper {
    flex-shrink: 0;
}

.document-card-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
}

.document-icon-pdf {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.document-icon-doc,
.document-icon-docx {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.document-icon-jpg,
.document-icon-jpeg,
.document-icon-png,
.document-icon-gif {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.document-card-info {
    flex: 1;
    min-width: 0;
}

.document-card-name {
    font-size: 1rem;
    font-weight: 700;
    color: #212529 !important;
    margin: 0 0 0.75rem 0;
    word-break: break-word;
}

.document-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.document-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.document-badge-type {
    background: #e7f3ff;
    color: #0066cc !important;
}

.document-badge-success {
    background: #d1fae5;
    color: #065f46 !important;
}

.document-badge-warning {
    background: #fef3c7;
    color: #92400e !important;
}

.document-badge-danger {
    background: #fee2e2;
    color: #991b1b !important;
}

.document-card-body {
    padding: 1rem 1.25rem;
}

.document-card-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.document-detail-item {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #6c757d !important;
}

.document-detail-item i {
    color: #667eea !important;
    width: 18px;
}

.document-card-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.document-action-btn {
    flex: 1;
    min-width: 100px;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

.document-action-btn:active {
    transform: scale(0.95);
}

.document-action-view {
    background: #e7f3ff;
    color: #0066cc !important;
}

.document-action-download {
    background: #f0f8ff;
    color: #667eea !important;
}

.document-action-delete {
    background: #fee2e2;
    color: #dc2626 !important;
}

/* Empty State */
.documents-empty-state {
    background: white;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.documents-empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.documents-empty-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.75rem;
}

.documents-empty-text {
    color: #6c757d !important;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

/* Pagination */
.documents-pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

/* Alert Styles */
.app-alert {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.app-alert-danger {
    background: #fee2e2;
    color: #991b1b !important;
    border: 1px solid #fecaca;
}

.app-alert-danger i {
    color: #dc2626 !important;
}

.app-alert ul {
    margin: 0;
    padding-left: 1.25rem;
}

/* Responsive */
@media (max-width: 576px) {
    .upload-document-card {
        padding: 1.25rem;
    }
    
    .upload-card-icon {
        width: 56px;
        height: 56px;
        font-size: 1.75rem;
    }
    
    .upload-card-title {
        font-size: 1.1rem;
    }
    
    .document-category-card {
        padding: 1rem;
    }
    
    .document-category-icon {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
    }
    
    .document-category-count {
        font-size: 1.5rem;
    }
    
    .document-card-header {
        padding: 1rem;
    }
    
    .document-card-icon {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
    }
    
    .documents-empty-state {
        padding: 2rem 1rem;
    }
    
    .documents-empty-icon {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }
    
    .document-action-btn {
        min-width: auto;
        flex: 1;
    }
}
</style>

<script>
function updateFileName(input) {
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        fileName.textContent = input.files[0].name;
        fileNameDisplay.style.display = 'flex';
    } else {
        fileNameDisplay.style.display = 'none';
    }
}

function deleteDocument(documentId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = '/documents/' + documentId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection
