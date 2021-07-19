<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillHistory extends Model
{
    protected $table = 'bill_history';
    public $timestamps = false;

    use HasFactory;
}
