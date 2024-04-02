<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileName',
        'mimeType',
        'extension',
        'fileSize',
        'slug',
        'status',
        'user_id',
        'name',
        'description',
        'category',
        'keywords'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
