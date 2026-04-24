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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Enum\Severity;

class Eventlog extends DeviceRelatedModel
{
    protected $table = 'eventlog';
    protected $primaryKey = 'event_id';
    public $timestamps = false;
    protected $fillable = ['datetime', 'device_id', 'message', 'type', 'reference', 'username', 'severity'];

    /**
     * @return array{severity: 'LibreNMS\Enum\Severity'}
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

        try {
            $this->save_log($log, $device);
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), 'Incorrect string value')) {
                throw $e;
            }

            $convertedText = self::convert_gbk2utf8($text);
            if ($convertedText === null || $convertedText === $text) {
                throw $e;
            }

            $log->message = $convertedText;
            $this->save_log($log, $device);
        }
    }

    private function save_log(self $log, Device|int|null $device): void
    {
        if ($device instanceof Device) {
            $device->eventlogs()->save($log);
        } else {
            $log->save();
        }
    }

    private static function convert_gbk2utf8(string $text): ?string
    {
        if (mb_check_encoding($text, 'UTF-8')) {
            return null;
        }

        foreach (['GB18030', 'GBK', 'GB2312'] as $encoding) {
            $converted = @mb_convert_encoding($text, 'UTF-8', $encoding);
            if (! is_string($converted) || $converted === '' || ! mb_check_encoding($converted, 'UTF-8')) {
                continue;
            }

            $roundTrip = @mb_convert_encoding($converted, $encoding, 'UTF-8');
            if ($roundTrip === $text) {
                return $converted;
            }
        }

        return null;
    }

    // ---- Define Relationships ----

    public function related(): MorphTo
    {
        return $this->morphTo('related', 'type', 'reference');
    }
}
