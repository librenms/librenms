<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentPref extends Model
{
    public $timestamps = false;
    protected $fillable = ['component', 'attribute', 'value'];

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : (string) $value;
    }
}
