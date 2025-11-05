<?php

namespace App\Models;

<<<<<<< master
class Customoid extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $primaryKey = 'customoid_id';
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customoid extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'customoid_id';
    protected $table = 'customoids';
<<<<<<< master
>>>>>>> Update Customoid.php
}
=======
}
>>>>>>> Update Customoid.php
