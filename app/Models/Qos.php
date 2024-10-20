<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Qos extends Model
{
    use HasFactory;

    public function children(): HasMany
    {
        return $this->hasMany(Qos::class, 'id', 'parent_id');
    }
}
