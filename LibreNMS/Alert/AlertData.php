<?php

/**
 * AlertData.php
 *
 * Alert Data Transfer Object
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

namespace LibreNMS\Alert;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use LibreNMS\Enum\AlertRuleOperationPhase;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Time;

/**
 * @implements ArrayAccess<string, mixed>
 * @implements Arrayable<string, mixed>
 */
class AlertData implements ArrayAccess, Arrayable, JsonSerializable
{
    public ?string $hostname = null;
    public ?int $device_id = null;
    public ?string $sysDescr = null;
    public ?string $sysName = null;
    public ?string $sysContact = null;
    public ?string $os = null;
    public ?string $type = null;
    public ?string $ip = null;
    public ?string $display = null;
    public ?string $version = null;
    public ?string $hardware = null;
    public ?string $features = null;
    public ?string $serial = null;
    public bool|int|null $status = null;
    public ?string $status_reason = null;
    public ?string $location = null;
    public ?string $description = null;
    public ?string $notes = null;
    public ?string $alert_notes = null;
    public ?int $uptime = null;
    public ?string $uptime_short = null;
    public ?string $uptime_long = null;
    public ?string $title = null;
    public ?string $elapsed = null;
    public int|bool|null $alerted = null;
    public int|string|null $alert_id = null;
    public int|string|null $rule_id = null;
    public int|string|null $id = null;
    public ?string $proc = null;
    /** @var array<int|string, mixed> */
    public array $faults = [];
    public int|string|null $uid = null;
    public ?string $severity = null;
    public ?string $rule = null;
    public ?string $name = null;
    public ?string $string = null;
    public ?string $timestamp = null;
    /** @var array<string, string>|null */
    public ?array $contacts = null;
    public AlertState|int|null $state = null;
    public ?string $msg = null;
    public mixed $builder = null;
    /** @var array<int|string, string>|null */
    public ?array $device_groups = null;
    public ?int $ping_timestamp = null;
    public float|int|null $ping_loss = null;
    public float|int|null $ping_min = null;
    public float|int|null $ping_max = null;
    public float|int|null $ping_avg = null;
    public ?string $debug = null;
    public mixed $applications = null;
    /** @var array<string, mixed>|null */
    public ?array $applications_metrics = null;
    /** @var array<string, mixed>|null */
    public ?array $diff = null;
    public ?string $transport = null;
    public ?string $transport_name = null;
    public AlertRuleOperationPhase|string|int|null $operation_phase = null;
    public ?int $escalation_step = null;
    public mixed $template = null;
    /** @var array<string, mixed>|null */
    public ?array $info = null;
    public mixed $extra = null;
    public ?string $query = null;

    /**
     * @param  array<string, mixed>|self  $data
     */
    public function __construct(array|self $data = [])
    {
        if ($data instanceof self) {
            $data = $data->toArray();
        }

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @param  array<string, mixed>|self  $data
     */
    public static function fromArray(array|self $data): self
    {
        return new self($data);
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return "$name is not a valid \$alert data name";
    }

    public function __isset(string $name): bool
    {
        return property_exists($this, $name) && isset($this->{$name});
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && property_exists($this, $offset) && isset($this->{$offset});
    }

    public function offsetGet(mixed $offset): mixed
    {
        return is_string($offset) ? $this->__get($offset) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_string($offset) && property_exists($this, $offset)) {
            $this->{$offset} = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset) && property_exists($this, $offset)) {
            $this->{$offset} = null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  array<int|string, mixed>  $faults
     * @return array<string, mixed>
     */
    public static function testData(Device $device, array $faults = []): array
    {
        return [
            'hostname' => $device->hostname,
            'device_id' => $device->device_id,
            'sysDescr' => $device->sysDescr,
            'sysName' => $device->sysName,
            'sysContact' => $device->sysContact,
            'os' => $device->os,
            'type' => $device->type,
            'ip' => $device->ip,
            'display' => $device->display,
            'version' => $device->version,
            'hardware' => $device->hardware,
            'features' => $device->features,
            'serial' => $device->serial,
            'status' => $device->status,
            'status_reason' => $device->status_reason,
            'location' => (string) $device->location,
            'description' => $device->purpose,
            'notes' => $device->notes,
            'uptime' => $device->uptime,
            'uptime_short' => Time::formatInterval($device->uptime, true),
            'uptime_long' => Time::formatInterval($device->uptime),
            'title' => 'Testing transport from ' . LibrenmsConfig::get('project_name'),
            'elapsed' => '11s',
            'alerted' => 0,
            'alert_id' => '000',
            'alert_notes' => 'This is the note for the test alert',
            'proc' => 'This is the procedure for the test alert',
            'rule_id' => '000',
            'id' => '000',
            'faults' => $faults,
            'uid' => '000',
            'severity' => 'critical',
            'rule' => 'macros.device = 1',
            'name' => 'Test-Rule',
            'string' => '#1: test => string;',
            'timestamp' => date('Y-m-d H:i:s'),
            'contacts' => AlertUtil::getContacts([$device->toArray()]),
            'state' => AlertState::ACTIVE,
            'msg' => 'This is a test alert',
            'builder' => '{}',
        ];
    }
}
