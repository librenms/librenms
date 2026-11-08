<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jobs recorded by the queue failer (queue.failed), with any queue driver
 */
class FailedJob extends Model
{
    public $timestamps = false;
    protected $table = 'failed_jobs';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'failed_at' => 'datetime',
        ];
    }
}
