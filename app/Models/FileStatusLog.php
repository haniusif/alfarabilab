<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileStatusLog extends Model
{
    protected $fillable = [
        'patient_file_id', 'user_id', 'from_status', 'to_status', 'note',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(PatientFile::class, 'patient_file_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
