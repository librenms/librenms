<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function widgets()
    {
        return $this->hasMany('App\Models\UserWidget', 'dashboard_id');
    }
}
