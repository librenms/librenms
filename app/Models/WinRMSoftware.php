<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinRMSoftware extends Model
{
    use HasFactory;

    protected $table = 'winrm_software';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
