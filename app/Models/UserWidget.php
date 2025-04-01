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

    protected function casts(): array
    {
        return [
            'settings' => 'array'
        ];
    }

    // ---- Define Relationships ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class, 'dashboard_id');
    }
}
