<?php

namespace LibreNMS\Applications;

use App\Facades\PortCache;
use App\Models\Application;
use App\Models\Storage;
use Illuminate\Support\Facades\Gate;

class Nfs extends ApplicationDefinition
{
    public const string TYPE = 'nfs';
    protected const ?string DOCS = 'NFS';
    protected const ?string NAME = 'NFS';

    protected const ?array OVERVIEW = ['server_rpc', 'server_cache', 'client_rpc', 'client_cache'];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $sections = ['' => 'General'];
        if (! empty($data['is_client']) && ! empty($data['mounts'])) {
            $sections['mounts'] = 'Mounts';
        }
        if (! empty($data['is_server']) && ! empty($data['mounted_by'])) {
            $sections['mounted_by'] = 'Mounted By';
        }

        $section = $page->option('app_page');
        if (count($sections) > 1) {
            $page->addFilter('NFS', 'app_page', $sections, $section === 'general' ? null : $section);
        }

        match ($section) {
            'mounts' => $this->mounts($page, $data['mounts'] ?? []),
            'mounted_by' => $this->mountedBy($page, $application, $data['mounted_by'] ?? []),
            default => $this->generalGraphs($page, $data),
        };
    }

    /** @param  array<string, mixed>  $data */
    private function generalGraphs(ApplicationPage $page, array $data): void
    {
        require base_path('includes/nfs-shared.inc.php');

        $os = $data['os'] ?? '';
        $graphs = [];
        if (! empty($data['is_server'])) {
            $graphs['server_cache'] = 'Server Cache';
            $graphs += match ($os) {
                'linux' => [
                    'server_rpc_info' => 'Server General RPC Stats',
                    'server_RAcache' => 'Server Read Ahead Cache',
                    'server_network' => 'Server Network',
                    'server_io' => 'Server IO',
                ],
                'freebsd' => ['server_general' => 'Server General'],
                default => [],
            };
            $graphs += $this->rpcGraphs('server', count($nfs_graphs['server_rpc'][$os] ?? $nfs_graphs['server_rpc'][''] ?? []));
        }
        if (! empty($data['is_client'])) {
            $graphs['client_rpc_info'] = 'Client General RPC Stats';
            $graphs += match ($os) {
                'freebsd' => ['client_cache' => 'Client Cache', 'client_general' => 'Client General'],
                'linux' => ['client_network' => 'Client Network'],
                default => [],
            };
            $graphs += $this->rpcGraphs('client', count($nfs_graphs['client_rpc'][$os] ?? $nfs_graphs['client_rpc'][''] ?? []));
        }
        $page->addGraphs($graphs, ['nfs_os' => $os]);
    }

    /** @return array<string, string> */
    private function rpcGraphs(string $role, int $count): array
    {
        $title = ucfirst($role) . ' RPC';
        $graphs = [$role . '_rpc1' => $title];
        for ($part = 2; $part <= min(9, (int) ceil($count / 12)); $part++) {
            $graphs[$role . '_rpc' . $part] = $title . ', continued';
        }

        return $graphs;
    }

    /** @param  list<array<string, mixed>>  $mounts */
    private function mountedBy(ApplicationPage $page, Application $application, array $mounts): void
    {
        $rows = [];
        $ports = [];
        $storage = $application->device->storage()->get()->keyBy('storage_descr');
        foreach ($mounts as $mount) {
            $host = (string) ($mount['host'] ?? '');
            if (! array_key_exists($host, $ports)) {
                $ports[$host] = PortCache::getByIp($host);
            }
            $path = $mount['path'] ?? '';
            $mountPoint = $path;
            while ($mountPoint !== '' && ! $storage->has($mountPoint) && dirname($mountPoint) !== $mountPoint) {
                $mountPoint = dirname($mountPoint);
            }
            $rows[] = [
                view('apps.port-reference', ['label' => $host, 'port' => $ports[$host]]),
                view('apps.storage-usage', ['path' => $path, 'storage' => $storage->get($mountPoint)]),
            ];
        }

        $page->addTable('Mounted By', ['Host', 'Path'], $rows);
    }

    /** @param  list<array<string, mixed>>  $mounts */
    private function mounts(ApplicationPage $page, array $mounts): void
    {
        $show = $page->option('show_mount_options') !== '0';
        $flat = $page->option('flat_mount_options') !== '0';
        $page->addFilter('Mount Options', 'show_mount_options', [1 => 'Show', 0 => 'Hide'], $show ? '1' : '0', [
            'app_page' => 'mounts', 'flat_mount_options' => (int) $flat,
        ]);
        if ($show) {
            $page->addFilter('Format', 'flat_mount_options', [1 => 'Flat', 0 => 'Table'], $flat ? '1' : '0', [
                'app_page' => 'mounts', 'show_mount_options' => 1,
            ]);
        }

        $headers = ['Host', 'Remote Path', 'Local Path'];
        if ($show) {
            $headers[] = 'Mount Options';
        }
        $rows = [];
        $ports = [];
        $storage = [];
        foreach ($mounts as $mount) {
            $host = (string) ($mount['host'] ?? '');
            if (! array_key_exists($host, $ports)) {
                $ports[$host] = PortCache::getByIp($host);
            }
            $port = $ports[$host];
            $path = $mount['rpath'] ?? '';
            $remote = null;
            if ($port?->device && Gate::allows('view', $port->device)) {
                $storage[$port->device_id] ??= $port->device->storage()->get()->keyBy('storage_descr');
                /** @var Storage|null $remote */
                $remote = $storage[$port->device_id]->get($path);
            }

            $row = [
                view('apps.port-reference', ['label' => $host, 'port' => $port]),
                view('apps.storage-usage', ['path' => $path, 'storage' => $remote]),
                $mount['lpath'] ?? '',
            ];
            if ($show) {
                $row[] = $this->mountOptions($mount, $flat);
            }
            $rows[] = $row;
        }

        $page->addTable('Mounts', $headers, $rows);
    }

    /** @param  array<string, mixed>  $mount */
    private function mountOptions(array $mount, bool $flat): string|\Illuminate\Contracts\Support\Htmlable
    {
        $flags = $mount['flags'] ?? [];
        $options = $mount['opts'] ?? [];
        if ($flat) {
            ksort($options);
            foreach ($options as $key => $value) {
                $flags[] = "$key=$value";
            }

            return implode(', ', $flags);
        }

        return view('apps.nfs-mount-options', ['flags' => $flags, 'options' => $options]);
    }
}
