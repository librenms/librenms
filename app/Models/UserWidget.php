<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWidget extends Model
{
    public $timestamps = false;
    protected $table = 'users_widgets';
    protected $primaryKey = 'user_widget_id';
    protected $fillable = ['user_id', 'widget_id', 'col', 'row', 'size_x', 'size_y', 'title', 'refresh', 'settings', 'dashboard_id'];
    protected $casts = ['settings' => 'array'];

    // ---- Define Relationships ----

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function widget()
    {
        return $this->hasOne(\App\Models\Widget::class, 'widget_id');
    }

    public function dashboard()
    {
        return $this->belongsTo(\App\Models\Dashboard::class, 'dashboard_id');
    }
}
