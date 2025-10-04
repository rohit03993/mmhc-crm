@extends('auth::layout')

@section('title', 'My Documents - MMHC CRM')
@section('page-title', 'My Documents')

@section('content')
<div class="row">
    <!-- Upload Document Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-upload me-2"></i>
                    Upload New Document
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="document_type" class="form-label">Document Type</label>
                            <select class="form-select @error('document_type') is-invalid @enderror" 
                                    id="document_type" 
                                    name="document_type" 
                                    required>
                                <option value="">Select Type</option>
                                <option value="certificate" {{ old('document_type') == 'certificate' ? 'selected' : '' }}>
                                    Certificate
                                </option>
                                <option value="id_proof" {{ old('document_type') == 'id_proof' ? 'selected' : '' }}>
                                    ID Proof
                                </option>
                                <option value="medical_license" {{ old('document_type') == 'medical_license' ? 'selected' : '' }}>
                                    Medical License
                                </option>
                                <option value="insurance" {{ old('document_type') == 'insurance' ? 'selected' : '' }}>
                                    Insurance
                                </option>
                                <option value="other" {{ old('document_type') == 'other' ? 'selected' : '' }}>
                                    Other
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="document_name" class="form-label">Document Name</label>
                            <input type="text" 
                                   class="form-control @error('document_name') is-invalid @enderror" 
                                   id="document_name" 
                                   name="document_name" 
                                   value="{{ old('document_name') }}" 
                                   placeholder="e.g., Nursing Certificate"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="document_file" class="form-label">Choose File</label>
                            <input type="file" 
                                   class="form-control @error('document_file') is-invalid @enderror" 
                                   id="document_file" 
                                   name="document_file" 
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   required>
                            <small class="text-muted">Max size: 10MB. Allowed: PDF, JPG, PNG, DOC, DOCX</small>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    My Documents
                </h5>
            </div>
            <div class="card-body">
                @if($documents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Status</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $document)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $document->file_icon }} me-2"></i>
                                                <div>
                                                    <strong>{{ $document->document_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $document->original_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $document->document_type_display }}
                                            </span>
                                        </td>
                                        <td>{{ $document->file_size }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($document->status == 'verified') bg-success
                                                @elseif($document->status == 'rejected') bg-danger
                                                @else bg-warning @endif">
                                                {{ $document->status_display }}
                                            </span>
                                        </td>
                                        <td>{{ $document->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('documents.download', $document) }}" 
                                                   class="btn btn-outline-primary" 
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteDocument({{ $document->id }})" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($documents->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $documents->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No documents uploaded yet</h5>
                        <p class="text-muted">Upload your first document using the form above.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Document</h5>
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
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteDocument(documentId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = '/documents/' + documentId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection
