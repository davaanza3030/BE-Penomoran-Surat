<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutgoingLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_number',
        'date',
        'recipient',
        'recipient_abbreviation',
        'category_id',
        'subject',
        'description',
        'nomor_surat',
        'attachments',
        'created_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

