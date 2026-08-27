<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Util\Url;

class PortsSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        if (! LibrenmsConfig::get('webui.global_search.ports')) {
            return [null];
        }

        $ports = Port::hasAccess($user)->with('device')->where('deleted', 0)
            ->select('ports.*')
            ->where(function (Builder $q) use ($like): void {
                $q->where('ifAlias', 'like', $like)
                    ->orWhere('ifDescr', 'like', $like)
                    ->orWhere('ifName', 'like', $like)
                    ->orWhere('portName', 'like', $like)
                    ->orWhere('port_descr_descr', 'like', $like)
                    ->orWhere('ifPhysAddress', 'like', $like);

                $mac = strtolower(str_replace([':', '-', '.'], '', (string) $like));
                if (ctype_xdigit($mac)) {
                    $q->orWhere('ifPhysAddress', 'like', '%' . $mac . '%');
                }
            })
            ->orderBy('ifDescr')->limit($limit)->get()
            ->map(fn (Port $port) => [
                'name' => $port->getLabel(),
                'subtitle' => implode(' · ', array_filter([
                    $port->device?->display,
                    $port->getDescription(),
                ])),
                'icon' => 'fa fa-link',
                'status' => match (true) {
                    (bool) $port->ignore => 'tw:border-l-black!',
                    $port->ifAdminStatus == IfOperStatus::Down => 'tw:border-l-gray-400!',
                    $port->ifOperStatus != IfOperStatus::Up => 'tw:border-l-red-600!',
                    default => 'tw:border-l-green-600!',
                },
                'url' => Url::portUrl($port),
            ]);

        return [$ports->isEmpty() ? null : ['type' => 'ports', 'label' => __('search.ports'), 'results' => $ports]];
    }
}
