<?php

namespace App\Models;

use Auth;
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
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function widget()
    {
        return $this->hasOne('App\Models\Widget', 'widget_id');
    }

    public function dashboard()
    {
        return $this->belongsTo('App\Models\Dashboard', 'dashboard_id');
    }
}
