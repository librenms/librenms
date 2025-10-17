<?php

namespace App\Models;

<<<<<<< master
class Customoid extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $primaryKey = 'customoid_id';
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customoid extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'customoid_id';
    protected $table = 'customoids';
>>>>>>> Update Customoid.php
}
