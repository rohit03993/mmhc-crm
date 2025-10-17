<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'section',
        'title',
        'subtitle',
        'content',
        'image_path',
        'is_active',
        'meta_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta_data' => 'array',
        'content' => 'array', // Auto-decode JSON content
    ];

    /**
     * Get content by section name
     */
    public static function getSection(string $section)
    {
        return self::where('section', $section)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active sections
     */
    public static function getAllSections()
    {
        return self::where('is_active', true)->get()->keyBy('section');
    }
}

