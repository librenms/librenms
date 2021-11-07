<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinRMServices extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['service_name', 'display_name', 'alerts'];
    protected $primaryKey = 'id';
    protected $table = 'winrm_services';
}
