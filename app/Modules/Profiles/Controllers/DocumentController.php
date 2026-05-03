<?php

namespace App\Modules\Profiles\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Profiles\Models\Document;
use App\Modules\Profiles\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $allowedDocumentTypes = $this->getAllowedDocumentTypes($user);

            // Get category counts for staff (nurses/caregivers)
            $categoryCounts = [];
            if ($user->isStaff()) {
                $allDocuments = Document::where('user_id', $user->id)->get();
                $categoryCounts = [
                    'certificate' => $allDocuments->where('document_type', 'certificate')->count(),
                    'id_proof' => $allDocuments->where('document_type', 'id_proof')->count(),
                    'medical_license' => $allDocuments->where('document_type', 'medical_license')->count(),
                    'insurance' => $allDocuments->where('document_type', 'insurance')->count(),
                ];
            }

            return view('profiles::documents.index', compact('user', 'documents', 'allowedDocumentTypes', 'categoryCounts'));
        } catch (\Exception $e) {
            // Fallback if document service fails
            $user = Auth::user();
            $documents = collect(); // Empty collection
            $allowedDocumentTypes = $this->getAllowedDocumentTypes($user);
            $categoryCounts = [];

            return view('profiles::documents.index', compact('user', 'documents', 'allowedDocumentTypes', 'categoryCounts'));
        }
    }

    /**
     * Get allowed document types based on user role
     */
    protected function getAllowedDocumentTypes($user)
    {
        if ($user->isPatient()) {
            return [
                'medical_report' => 'Medical Report',
                'aadhaar_card' => 'Aadhaar Card',
                'past_medical_history' => 'Past Medical History',
                'prescription' => 'Prescription',
                'lab_report' => 'Lab Report',
                'insurance_card' => 'Insurance Card',
                'other' => 'Other',
            ];
        } elseif ($user->isStaff()) {
            return [
                'certificate' => 'Certificate',
                'id_proof' => 'ID Proof',
                'medical_license' => 'Medical License',
                'insurance' => 'Insurance',
                'other' => 'Other',
            ];
        } else {
            // Admin or other roles - allow all types
            return [
                'certificate' => 'Certificate',
                'id_proof' => 'ID Proof',
                'medical_license' => 'Medical License',
                'insurance' => 'Insurance',
                'medical_report' => 'Medical Report',
                'aadhaar_card' => 'Aadhaar Card',
                'past_medical_history' => 'Past Medical History',
                'prescription' => 'Prescription',
                'lab_report' => 'Lab Report',
                'insurance_card' => 'Insurance Card',
                'other' => 'Other',
            ];
        }
    }

    /**
     * Upload document
     */
    public function upload(Request $request)
    {
        $user = Auth::user();
        $allowedTypes = array_keys($this->getAllowedDocumentTypes($user));

        $validator = Validator::make($request->all(), [
            'document_type' => ['required', 'in:'.implode(',', $allowedTypes)],
            'document_name' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,jpeg,jpg,png,doc,docx|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $documentData = $request->only(['document_type', 'document_name']);

        $document = $this->documentService->uploadDocument($user, $request->file('document_file'), $documentData);

        return redirect()->back()
            ->with('success', 'Document uploaded successfully!');
    }

    /**
     * Delete document
     */
    public function delete(Document $document)
    {
        // Check if user owns this document
        if ((int) $document->user_id !== (int) Auth::id()
            && ! in_array(Auth::user()->role, ['admin', 'super_admin'], true)) {
            abort(403, 'Unauthorized access to document');
        }

        $this->documentService->deleteDocument($document);

        return redirect()->back()
            ->with('success', 'Document deleted successfully!');
    }

    /**
     * View/Preview document (for PDFs and images)
     */
    public function view($id)
    {
        $document = Document::findOrFail($id);

        $this->assertCanViewOrDownloadDocument($document);

        // Check if file exists in storage
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        $filePath = Storage::disk('public')->path($document->file_path);
        $mimeType = $document->mime_type ?? Storage::disk('public')->mimeType($document->file_path);

        // Check if file can be viewed in browser (PDF, images)
        $viewableTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        if (in_array($mimeType, $viewableTypes)) {
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="'.$document->original_name.'"',
            ]);
        }

        // For non-viewable files, redirect to download
        return redirect()->route('documents.download', $document->id);
    }

    /**
     * Download document
     */
    public function download($id)
    {
        $document = Document::findOrFail($id);

        $this->assertCanViewOrDownloadDocument($document);

        // Check if file exists in storage
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        $filePath = Storage::disk('public')->path($document->file_path);

        return response()->download($filePath, $document->original_name);
    }

    /**
     * Owner, CRM admins, or academic staff who may view this student's full report may open files.
     */
    protected function assertCanViewOrDownloadDocument(Document $document): void
    {
        $auth = Auth::user();
        if ((int) $document->user_id === (int) $auth->id) {
            return;
        }
        if (in_array($auth->role, ['admin', 'super_admin'], true)) {
            return;
        }

        $owner = User::find($document->user_id);
        if (! $owner || $owner->role !== 'student') {
            abort(403, 'Unauthorized access to document');
        }

        if ($auth->role === 'institution_admin' && $auth->academic_institution_id) {
            $allowed = DB::table('academic_batch_users')
                ->join('academic_batches', 'academic_batches.id', '=', 'academic_batch_users.batch_id')
                ->where('academic_batch_users.user_id', $owner->id)
                ->where('academic_batch_users.type', 'student')
                ->where('academic_batches.institution_id', $auth->academic_institution_id)
                ->exists();
            if ($allowed) {
                return;
            }
        }

        if ($auth->role === 'faculty') {
            $facultyBatchIds = DB::table('academic_batch_users')
                ->where('user_id', $auth->id)
                ->where('type', 'faculty')
                ->pluck('batch_id');
            $allowed = DB::table('academic_batch_users')
                ->where('user_id', $owner->id)
                ->where('type', 'student')
                ->whereIn('batch_id', $facultyBatchIds)
                ->exists();
            if ($allowed) {
                return;
            }
        }

        abort(403, 'Unauthorized access to document');
    }
}
