<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinRMProcesses extends Model
{
    use HasFactory;

    protected $table = 'winrm_processes';
    protected $primaryKey = 'id';
}
