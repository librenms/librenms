<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWidget extends Model
{
    public $timestamps = false;
    protected $table = 'users_widgets';
    protected $primaryKey = 'user_widget_id';
    protected $fillable = ['user_id', 'widget', 'col', 'row', 'size_x', 'size_y', 'title', 'refresh', 'settings', 'dashboard_id'];

    /**
     * @return array{settings: 'array'}
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dashboard, $this>
     */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class, 'dashboard_id');
    }
}
