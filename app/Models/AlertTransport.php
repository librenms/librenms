<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Alert\Transport;

/**
 * \App\Models\AlertTransport
 *
 * @property int $transport_id
 * @property string $transport_name
 * @property string $transport_type
 * @property bool $is_default
 * @property array|null $transport_config
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport query()
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport whereTransportConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport whereTransportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport whereTransportName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlertTransport whereTransportType($value)
 * @mixin \Eloquent
 */
class AlertTransport extends Model
{
    use HasFactory;

    protected $primaryKey = 'transport_id';
    public $timestamps = false;
    protected $casts = [
        'is_default' => 'boolean',
        'transport_config' => 'array',
    ];

    public function instance(): Transport
    {
        $class = Transport::getClass($this->transport_type);

        return new $class($this);
    }
}
