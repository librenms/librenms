<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Util\Url;

class PortsSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $ports = Port::hasAccess($user)->with('device')->where('deleted', 0)
            ->where(fn (Builder $q) => $q->where('ifAlias', 'like', $like)
                ->orWhere('ifDescr', 'like', $like)
                ->orWhere('ifName', 'like', $like)
                ->orWhere('portName', 'like', $like)
                ->orWhere('port_descr_descr', 'like', $like))
            ->orderBy('ifDescr')->limit($limit)->get()
            ->map(fn (Port $p) => [
                'name' => $p->getLabel(),
                'subtitle' => trim($p->device?->display . ' ' . $p->getDescription()),
                'icon' => 'fa fa-link',
                'status' => match (true) {
                    (bool) $p->ignore => 'tw:border-l-black!',
                    $p->ifAdminStatus == IfOperStatus::Down => 'tw:border-l-gray-400!',
                    $p->ifOperStatus != IfOperStatus::Up => 'tw:border-l-red-600!',
                    default => 'tw:border-l-green-600!',
                },
                'url' => Url::portUrl($p),
            ]);

        return [$ports->isEmpty() ? null : ['type' => 'ports', 'label' => __('Ports'), 'results' => $ports]];
    }
}
