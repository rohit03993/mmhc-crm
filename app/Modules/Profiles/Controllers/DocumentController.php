<?php

namespace App\Modules\Profiles\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Profiles\Models\Document;
use App\Modules\Profiles\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Show user documents
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $documents = $this->documentService->getUserDocuments($user);
            
            return view('profiles::documents.index', compact('user', 'documents'));
        } catch (\Exception $e) {
            // Fallback if document service fails
            $user = Auth::user();
            $documents = collect(); // Empty collection
            
            return view('profiles::documents.index', compact('user', 'documents'));
        }
    }

    /**
     * Upload document
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:certificate,id_proof,medical_license,insurance,other',
            'document_name' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,jpeg,jpg,png,doc,docx|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $documentData = $request->only(['document_type', 'document_name']);
        
        $document = $this->documentService->uploadDocument($user, $request->file('document_file'), $documentData);

        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully!');
    }

    /**
     * Delete document
     */
    public function delete(Document $document)
    {
        // Check if user owns this document
        if ($document->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to document');
        }

        $this->documentService->deleteDocument($document);

        return redirect()->back()
            ->with('success', 'Document deleted successfully!');
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        // Check if user owns this document or is admin
        if ($document->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to document');
        }

        if (!Storage::exists($document->file_path)) {
            abort(404, 'Document not found');
        }

        return Storage::download($document->file_path, $document->original_name);
    }
}
