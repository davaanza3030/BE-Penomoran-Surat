<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_number',
        'subject',
        'date',
        'type',
        'category_id',
        'created_by',
        'sender',
        'recipient',
        'attachments',
        'description',
    ];

    // Relasi ke kategori
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relasi ke user yang membuat surat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke number format
}
