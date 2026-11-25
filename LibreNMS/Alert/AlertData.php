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
use App\Models\AlertTemplate;
use App\Models\Device;
use ArrayAccess;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Time;
use LogicException;

/**
 * @implements ArrayAccess<string, mixed>
 * @implements Arrayable<string, mixed>
 */
class AlertData implements ArrayAccess, Arrayable, JsonSerializable
{
    public private(set) ?string $hostname = null;
    public private(set) ?int $device_id = null;
    public private(set) ?string $sysDescr = null;
    public private(set) ?string $sysName = null;
    public private(set) ?string $sysContact = null;
    public private(set) ?string $os = null;
    public private(set) ?string $type = null;
    public private(set) ?string $ip = null;
    public private(set) ?string $display = null;
    public private(set) ?string $version = null;
    public private(set) ?string $hardware = null;
    public private(set) ?string $features = null;
    public private(set) ?string $serial = null;
    public private(set) bool|int|null $status = null;
    public private(set) ?string $status_reason = null;
    public private(set) ?string $location = null;
    public private(set) ?string $description = null;
    public private(set) ?string $notes = null;
    public private(set) ?string $alert_notes = null;
    public private(set) ?int $uptime = null;
    public private(set) ?string $uptime_short = null;
    public private(set) ?string $uptime_long = null;
    public private(set) ?string $title = null;
    public private(set) ?string $elapsed = null;
    public private(set) int|bool|null $alerted = null;
    public private(set) ?int $alert_id = null;
    public private(set) ?int $rule_id = null;
    public private(set) ?int $id = null;
    public private(set) ?string $proc = null;
    /** @var array<int|string, mixed> */
    public private(set) array $faults = [];
    public private(set) ?int $uid = null;
    public private(set) ?string $severity = null;
    public private(set) ?string $rule = null;
    public private(set) ?string $name = null;
    public private(set) ?string $string = null;
    public private(set) ?string $timestamp = null;
    /** @var array<string, string>|null */
    public private(set) ?array $contacts = null;
    public private(set) ?int $state = null;
    public private(set) ?string $msg = null;
    public private(set) mixed $builder = null;
    /** @var array<int|string, string>|null */
    public private(set) ?array $device_groups = null;
    public private(set) Carbon|int|null $ping_timestamp = null;
    public private(set) float|int|null $ping_loss = null;
    public private(set) float|int|string|null $ping_min = null;
    public private(set) float|int|string|null $ping_max = null;
    public private(set) float|int|null $ping_avg = null;
    public private(set) ?string $debug = null;
    public private(set) mixed $applications = null;
    /** @var array<string, mixed>|null */
    public private(set) ?array $applications_metrics = null;
    /** @var array<string, mixed>|null */
    public private(set) ?array $diff = null;
    public private(set) ?string $transport = null;
    public private(set) ?string $transport_name = null;
    public private(set) ?string $operation_phase = null;
    public private(set) ?int $escalation_step = null;
    /** @var array<string, mixed>|null */
    public private(set) ?array $info = null;
    public private(set) mixed $extra = null;
    public private(set) ?string $query = null;

    /** @var array<string, true> */
    private array $providedFields = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * The alert as sent to one transport: the transport fields, then the rendered
     * title, then the rendered body (which may use the title and transport).
     */
    public function forTransport(?string $transport, ?string $transportName, Template $template): self
    {
        $alert = clone $this;
        $alert->set('transport', $transport);
        $alert->set('transport_name', $transportName);
        $alert->set('title', $template->getTitle($alert));
        $alert->set('msg', $template->getBody($alert));

        return $alert;
    }

    public function __get(string $name): mixed
    {
        if ($this->hasProperty($name)) {
            return $this->{$name};
        }

        return "$name is not a valid \$alert data name";
    }

    public function __isset(string $name): bool
    {
        return $this->hasProperty($name) && isset($this->{$name});
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->__isset($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        // Unlike __get (used by templates), array access mirrors a plain array: unknown keys are null.
        return is_string($offset) && $this->hasProperty($offset) ? $this->{$offset} : null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException('AlertData is immutable');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException('AlertData is immutable');
    }

    private function set(mixed $key, mixed $value): void
    {
        if (is_string($key) && $this->hasProperty($key)) {
            $this->{$key} = $value;
            $this->providedFields[$key] = true;
        }
    }

    private function hasProperty(string $name): bool
    {
        return $name !== 'providedFields' && property_exists($this, $name);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = get_object_vars($this);
        unset($data['providedFields']);

        return array_filter($data, fn ($value, $key) => isset($this->providedFields[$key])
            || ($value !== null && ($key !== 'faults' || $value !== [])), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * The array payload for legacy transports that only implement deliverAlert().
     *
     * @return array<string, mixed>
     */
    public function toTransportPayload(?AlertTemplate $template = null): array
    {
        $payload = $this->toArray();
        $payload['template'] = $template;
        $payload['alert'] = $this;

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  array<string, mixed>  $overrides  fields to set instead of the test values
     */
    public static function testData(Device $device, array $overrides = []): self
    {
        return new self([
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
            'alert_id' => 0,
            'alert_notes' => 'This is the note for the test alert',
            'proc' => 'This is the procedure for the test alert',
            'rule_id' => 0,
            'id' => 0,
            'faults' => [],
            'uid' => 0,
            'severity' => 'critical',
            'rule' => 'macros.device = 1',
            'name' => 'Test-Rule',
            'string' => '#1: test => string;',
            'timestamp' => date('Y-m-d H:i:s'),
            'contacts' => AlertUtil::getContacts([$device->toArray()]),
            'state' => AlertState::ACTIVE,
            'msg' => 'This is a test alert',
            'builder' => '{}',
            ...$overrides,
        ]);
    }
}
