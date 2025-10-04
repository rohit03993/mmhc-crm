<?php

namespace App\Modules\Profiles\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\User;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'document_name',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the user that owns the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeAttribute($value)
    {
        $bytes = $value;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get document type display name
     */
    public function getDocumentTypeDisplayAttribute()
    {
        return match($this->document_type) {
            'certificate' => 'Certificate',
            'id_proof' => 'ID Proof',
            'medical_license' => 'Medical License',
            'insurance' => 'Insurance',
            'other' => 'Other',
            default => ucfirst($this->document_type)
        };
    }

    /**
     * Get document status display
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'uploaded' => 'Uploaded',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get document icon based on file type
     */
    public function getFileIconAttribute()
    {
        $extension = pathinfo($this->original_name, PATHINFO_EXTENSION);
        
        return match(strtolower($extension)) {
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc', 'docx' => 'fas fa-file-word text-primary',
            'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-success',
            default => 'fas fa-file text-secondary'
        };
    }

    /**
     * Scope for specific document type
     */
    public function scopeType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope for verified documents
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope for uploaded documents
     */
    public function scopeUploaded($query)
    {
        return $query->where('status', 'uploaded');
    }
}
