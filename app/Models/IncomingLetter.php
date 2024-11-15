<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_number',
        'date',
        'sender',
        'category_id',
        'subject',
        'description',
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

