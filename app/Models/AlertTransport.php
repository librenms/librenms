<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Alert\Transport;
use LibreNMS\Alert\Transport\Dummy;

class AlertTransport extends Model
{
    use HasFactory;

    protected $primaryKey = 'transport_id';
    public $timestamps = false;
    protected $fillable = ['transport_config'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'transport_config' => 'array',
        ];
    }

    public function instance(): Transport
    {
        $class = Transport::getClass($this->transport_type);

        if (class_exists($class)) {
            return new $class($this);
        }

        return new Dummy;
    }
}
