<?php

/**
 * Eventlog.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use App\Models\Traits\Filterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Enum\Severity;

class Eventlog extends DeviceRelatedModel
{
    use Filterable;
    use HasFactory;

    protected $table = 'eventlog';
    protected $primaryKey = 'event_id';
    public $timestamps = false;
    protected $fillable = ['datetime', 'device_id', 'message', 'type', 'reference', 'username', 'severity'];

    /** @var string[] */
    protected array $filterable = ['device_id', 'type', 'port', 'search'];

    /**
     * @param  Device|null  $device  limit the port choices to this device and leave out the device filter
     * @return array<array{key: string, label: string, type: string, search?: bool, endpoint?: string, params?: array<string, string|int>}>
     */
    public static function filterFieldDefinitions(?Device $device = null): array
    {
        $fields = [
            ['key' => 'search', 'label' => __('Message'), 'type' => 'text', 'search' => true],
            ['key' => 'type', 'label' => __('Type'), 'type' => 'select', 'endpoint' => route('ajax.select.eventlog'), 'params' => ['field' => 'type']],
            ['key' => 'port', 'label' => __('Port'), 'type' => 'select', 'endpoint' => route('ajax.select.port'), 'params' => $device ? ['device' => $device->device_id] : []],
        ];

        if ($device === null) {
            $fields[] = ['key' => 'device_id', 'label' => __('Device'), 'type' => 'select', 'endpoint' => route('ajax.select.device')];
        }

        return $fields;
    }

    /**
     * Match the message, or the label of the port of interface events
     *
     * @param  Builder<static>  $query
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     */
    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $positive = ['not' => false] + $config;

        // empty checks only apply to the message
        $ports = ($config['null'] ?? false) ? null : Port::query()->select('port_id')->where(function (Builder $query) use ($value, $positive): void {
            foreach (['ifName', 'ifAlias', 'ifDescr'] as $column) {
                $this->applyQueryLogic($query, $column, $value, $positive, 'or');
            }
        });

        $match = fn (Builder $query) => $query
            ->where(fn (Builder $query) => $this->applyQueryLogic($query, $this->qualifyColumn('message'), $value, $positive))
            ->when($ports, fn (Builder $query, $ports) => $query->orWhere(fn (Builder $query) => $query
                ->whereNotNull('eventlog.type')
                ->where('eventlog.type', 'interface')
                ->whereIn('eventlog.reference', $ports)));

        ($config['not'] ?? false) ? $query->whereNot($match) : $query->where($match);
    }

    /**
     * Interface events of the port
     *
     * @param  Builder<static>  $query
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     */
    public function filterPort(Builder $query, mixed $value, array $config): void
    {
        $positive = ['not' => false] + $config;
        $match = fn (Builder $query) => $query
            ->whereNotNull('eventlog.type')
            ->where('eventlog.type', 'interface')
            ->where(fn (Builder $query) => $this->applyQueryLogic($query, 'eventlog.reference', $value, $positive));

        ($config['not'] ?? false) ? $query->whereNot($match) : $query->where($match);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => Severity::class,
        ];
    }

    // ---- Helper Functions ----
    /**
     * This is used to be able to mock _log()
     *
     * @see _log()
     *
     * @param  string  $text  message describing the event
     * @param  Device|int|null  $device  related device
     * @param  string  $type  brief category for this event. Examples: sensor, state, stp, system, temperature, interface
     * @param  Severity  $severity  1: ok, 2: info, 3: notice, 4: warning, 5: critical, 0: unknown
     * @param  int|string|null  $reference  the id of the referenced entity.  Supported types: interface
     */
    public static function log(string $text, Device|int|null $device = null, ?string $type = null, Severity $severity = Severity::Info, int|string|null $reference = null): void
    {
        $model = app()->make(Eventlog::class);
        $model->_log($text, $device, $type, $severity, $reference);
    }

    /**
     * Log events to the event table
     */
    public function _log(string $text, Device|int|null $device = null, ?string $type = null, Severity $severity = Severity::Info, int|string|null $reference = null): void
    {
        $log = new static([
            'reference' => $reference,
            'type' => $type,
            'datetime' => Carbon::now(),
            'severity' => $severity,
            'message' => $text,
            'username' => (class_exists('\Auth') && Auth::check()) ? Auth::user()->username : '',
        ]);

        if (is_numeric($device)) {
            $log->device_id = $device;
        }

        if ($device instanceof Device) {
            $device->eventlogs()->save($log);
        } else {
            $log->save();
        }
    }

    // ---- Define Relationships ----

    public function related(): MorphTo
    {
        return $this->morphTo('related', 'type', 'reference');
    }
}
