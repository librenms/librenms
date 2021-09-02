<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dashboard extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'dashboard_id';
    protected $fillable = ['user_id', 'dashboard_name', 'access'];

    // ---- Helper Functions ---

    /**
     * @param User $user
     * @return bool
     */
    public function canRead($user)
    {
        return $this->user_id == $user->user_id || $this->access > 0;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function canWrite($user)
    {
        return $this->user_id == $user->user_id || $this->access > 1;
    }

    // ---- Query scopes ----

    /**
     * @param Builder $query
     * @param User $user
     * @return Builder|static
     */
    public function scopeAllAvailable(Builder $query, $user)
    {
        return $query->where('user_id', $user->user_id)
            ->orWhere('access', '>', 0);
    }

    // ---- Define Relationships ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(\App\Models\UserWidget::class, 'dashboard_id');
    }
}
