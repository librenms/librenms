<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'dashboard_id';
    protected $fillable = ['user_id', 'dashboard_name', 'access'];

    // ---- Query scopes ----

    /**
     * @param Builder $query
     * @param $user
     * @return Builder|static
     */
    public function scopeAllAvailable(Builder $query, $user)
    {
        return $query->where('user_id', $user->user_id)
            ->orWhere('access', '>', 0);
    }

    // ---- Define Reletionships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function widgets()
    {
        return $this->hasMany('App\Models\UserWidget', 'dashboard_id');
    }
}
