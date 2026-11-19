<?php

namespace App\Http\Controllers\Table;

use App\Models\Ipv4Mac;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Ipv4Mac>
 */
class ArpTableController extends TableController
{
    /** @var array<string, string> */
    protected array $default_sort = ['hostname' => 'asc'];

    /** @return array<string, string> */
    protected function rules(): array
    {
        return [
            'device_id' => 'nullable|integer',
            'port_id' => 'nullable|integer',
            'searchby' => 'nullable|string',
            'sort' => 'array',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        return Ipv4Mac::hasAccess($request->user())
            ->select('ipv4_mac.*')
            ->leftJoin('ports', 'ipv4_mac.port_id', '=', 'ports.port_id')
            ->leftJoin('devices', 'ipv4_mac.device_id', '=', 'devices.device_id')
            ->with('port.device', 'device.location', 'remote_ports_maybe.device.location');
    }

    /** @return array<string, string> */
    protected function filterFields(Request $request): array
    {
        return [
            'ipv4_mac.device_id' => 'device_id',
            'ipv4_mac.port_id' => 'port_id',
        ];
    }

    /** @return array<string, string> */
    protected function sortFields(Request $request): array
    {
        return [
            'hostname' => 'devices.hostname',
            'interface' => 'ports.ifDescr',
            'mac_address' => 'ipv4_mac.mac_address',
            'ipv4_address' => 'ipv4_mac.ipv4_address',
        ];
    }

    /** @return list<string> */
    protected function searchFields(Request $request): array
    {
        return match ($request->input('searchby')) {
            'ip' => ['ipv4_address'],
            'mac' => ['mac_address'],
            default => ['ipv4_address', 'mac_address'],
        };
    }

    /**
     * @param  Builder<Ipv4Mac>  $query
     * @param  list<string>  $fields
     * @return Builder<Ipv4Mac>
     */
    protected function search(?string $search, Builder $query, array $fields): Builder
    {
        if ($search) {
            $mac = str_replace([':', ' ', '-', '.', '0x'], '', $search);

            $query->where(function (Builder $query) use ($fields, $search, $mac): void {
                foreach ($fields as $field) {
                    $query->orWhereLike('ipv4_mac.' . $field, '%' . ($field === 'mac_address' ? $mac : $search) . '%');
                }
            });
        }

        return $query;
    }

    /**
     * @param  Ipv4Mac  $model
     */
    public function formatItem(Model $model): array
    {
        $port = $model->port;
        $remote = $model->remote_ports_maybe->first();
        $local = $remote?->port_id === $model->port_id;
        $mac = Mac::parse($model->mac_address);

        $interface = Url::portLink($port);
        if ($port && ($port->ifInErrors_delta > 0 || $port->ifOutErrors_delta > 0)) {
            $interface .= ' ' . Url::portLink($port, new HtmlString("<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>"), 'port_errors');
        }

        return [
            'mac_address' => $mac->readable(),
            'mac_oui' => $mac->vendor(),
            'ipv4_address' => $model->ipv4_address,
            'hostname' => Url::deviceLink($model->device),
            'interface' => $interface,
            'remote_device' => $local ? 'Localhost' : Url::deviceLink($remote?->device),
            'remote_interface' => $local ? 'Local port' : Url::portLink($remote),
        ];
    }
}
