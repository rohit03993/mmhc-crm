<?php

namespace App\Modules\Profiles\Services;

use App\Models\Core\User;
use App\Modules\Profiles\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Get user documents
     */
    public function getUserDocuments(User $user)
    {
        return Document::where('user_id', $user->id)
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
    }

    /**
     * Upload document
     */
    public function uploadDocument(User $user, UploadedFile $file, array $documentData): Document
    {
        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Store file
        $filePath = $file->storeAs(
            'documents/' . $user->id,
            $filename,
            'public'
        );

        // Create document record
        return Document::create([
            'user_id' => $user->id,
            'document_type' => $documentData['document_type'],
            'document_name' => $documentData['document_name'],
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'uploaded',
        ]);
    }

    /**
     * Delete document
     */
    public function deleteDocument(Document $document): bool
    {
        // Delete file from storage
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Delete document record
        return $document->delete();
    }

    /**
     * Get document statistics
     */
    public function getDocumentStats(): array
    {
        return [
            'total_documents' => Document::count(),
            'uploaded_documents' => Document::uploaded()->count(),
            'verified_documents' => Document::verified()->count(),
            'certificates' => Document::type('certificate')->count(),
            'id_proofs' => Document::type('id_proof')->count(),
            'medical_licenses' => Document::type('medical_license')->count(),
            'insurance_docs' => Document::type('insurance')->count(),
        ];
    }

    /**
     * Get documents by type
     */
    public function getDocumentsByType(string $type)
    {
        return Document::with('user')
                      ->type($type)
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);
    }

    /**
     * Update document status
     */
    public function updateDocumentStatus(Document $document, string $status): Document
    {
        $document->update(['status' => $status]);
        return $document;
    }

    /**
     * Get recent documents
     */
    public function getRecentDocuments(int $limit = 10)
    {
        return Document::with('user')
                      ->orderBy('created_at', 'desc')
                      ->limit($limit)
                      ->get();
    }

    /**
     * Search documents
     */
    public function searchDocuments(string $query, string $type = null)
    {
        $documents = Document::with('user');

        if ($type) {
            $documents->type($type);
        }

        return $documents->where(function ($q) use ($query) {
            $q->where('document_name', 'like', "%{$query}%")
              ->orWhere('original_name', 'like', "%{$query}%")
              ->orWhereHas('user', function($userQuery) use ($query) {
                  $userQuery->where('name', 'like', "%{$query}%")
                           ->orWhere('email', 'like', "%{$query}%");
              });
        })->orderBy('created_at', 'desc')->paginate(15);
    }
}
