<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKnowledgeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'tags',
        'is_active',
        'fingerprint',
        'source_type',
        'source_ref',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
