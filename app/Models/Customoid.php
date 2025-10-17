<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customoid extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'customoid_id';
    protected $table = 'customoids';
}
