<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationMetric extends Model
{
    use HasFactory;

    public $timestamps = false;

    // ---- Define Relationships ----

    public function app()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }
}
