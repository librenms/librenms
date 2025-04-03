<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationMetric extends Model
{
    use HasFactory;

    public $timestamps = false;

    // ---- Define Relationships ----

    public function app(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'app_id');
    }
}
