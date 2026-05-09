<?php

/**
 * DiscoveryNextHops.php
 *
 * Auto-creates pingonly child devices, one per "interesting" next-hop
 * found in a monitored device's routing table. Default route by default
 * (the ISP / WAN gateway), plus optional user-flagged supernets for
 * internal routers.
 *
 * Depends on the existing `route` discovery module: that module already
 * walks IP-FORWARD-MIB::inetCidrRouteTable (and friends) and persists the
 * data to the `routes` table. We just consume what's already there.
 *
 * Each newly-discovered next-hop:
 *   - is added as a pingonly device (snmp_disable=1, os=ping),
 *   - has the discovering device set as its parent (device_relationships),
 *   - so failures propagate cleanly via existing dependency-aware alerting.
 *
 * Why this matters: today, when an ISP gateway becomes unreachable, the
 * monitored router itself stays "up" in LibreNMS — the dashboard shows
 * green while no traffic actually leaves the network. This module makes
 * each next-hop a first-class monitored entity.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Kamil Bienkiewicz
 * @author     Kamil Bienkiewicz
 */

namespace LibreNMS\Modules;

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class DiscoveryNextHops implements Module
{
    /** Route type 4 = remote (per IANA / IP-FORWARD-MIB::inetCidrRouteType) */
    private const ROUTE_TYPE_REMOTE = 4;

    /** Sentinel "no next-hop" addresses we filter out */
    private const NULL_HOPS = ['0.0.0.0', '::', '0:0:0:0:0:0:0:0'];

    /** Sentinel default-route destinations */
    private const DEFAULT_DESTS = ['0.0.0.0', '::', '0:0:0:0:0:0:0:0'];

    /** Per-IP discovery debounce (seconds) — same pattern as DiscoveryArp */
    private const DEBOUNCE_TTL = 3600;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['route'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $only_default = (bool) LibrenmsConfig::get('autodiscovery.discovery-next-hops.only-default-route', true);
        $extra_supernets = (array) LibrenmsConfig::get('autodiscovery.discovery-next-hops.supernets', []);

        $candidates = $this->collectCandidates($device, $only_default, $extra_supernets);

        Log::info(sprintf(
            'Found %d next-hop candidate(s) on %s',
            count($candidates), $device->hostname
        ));

        $created = 0;
        $linked = 0;
        $skipped_existing = 0;
        $skipped_debounced = 0;

        foreach ($candidates as $hop_ip => $meta) {
            // Per-IP debounce: don't re-evaluate the same hop more than once per hour
            if (! Cache::add('next_hops_discovery:' . $hop_ip, true, self::DEBOUNCE_TTL)) {
                $skipped_debounced++;
                continue;
            }

            // If the hop is already a monitored device, just ensure parent linkage
            $existing = Device::where('hostname', $hop_ip)
                ->orWhere('overwrite_ip', $hop_ip)
                ->first();

            if ($existing) {
                if ($this->ensureParentRelationship($existing, $device)) {
                    $linked++;
                }
                $skipped_existing++;
                continue;
            }

            // Create new pingonly device
            if ($this->createPingonlyHop($hop_ip, $meta, $device)) {
                $created++;
            }
        }

        Log::info(sprintf(
            '  Next-hops: %d new, %d already-existing (linked: %d), %d debounced',
            $created, $skipped_existing, $linked, $skipped_debounced
        ));
    }

    /**
     * Filter the device's routes table down to interesting next-hops.
     *
     * @return array<string, array{route: \App\Models\Route, is_default: bool}>
     */
    private function collectCandidates(Device $device, bool $only_default, array $extra_supernets): array
    {
        $candidates = [];

        $routes = $device->routes()
            ->where('inetCidrRouteType', self::ROUTE_TYPE_REMOTE)
            ->get();

        foreach ($routes as $route) {
            $hop = $route->inetCidrRouteNextHop;
            if (in_array($hop, self::NULL_HOPS, true)) {
                continue;
            }

            $is_default = in_array($route->inetCidrRouteDest, self::DEFAULT_DESTS, true)
                       && (int) $route->inetCidrRoutePfxLen === 0;

            if (! $is_default) {
                if ($only_default) {
                    $supernet = $route->inetCidrRouteDest . '/' . $route->inetCidrRoutePfxLen;
                    if (! in_array($supernet, $extra_supernets, true)) {
                        continue;
                    }
                }
            }

            // Keep only one entry per unique next-hop IP. Prefer the default route
            // if both default and non-default share the same next-hop.
            if (! isset($candidates[$hop]) || $is_default) {
                $candidates[$hop] = [
                    'route' => $route,
                    'is_default' => $is_default,
                ];
            }
        }

        return $candidates;
    }

    /**
     * Create a new pingonly device for this next-hop and link it to its parent.
     */
    private function createPingonlyHop(string $hop_ip, array $meta, Device $parent): bool
    {
        $route = $meta['route'];
        $is_default = $meta['is_default'];

        $sysName = $is_default
            ? sprintf('Internet via %s', $parent->hostname)
            : sprintf(
                'Network %s/%s via %s',
                $route->inetCidrRouteDest,
                $route->inetCidrRoutePfxLen,
                $parent->hostname
            );

        $newDevice = new Device([
            'hostname' => $hop_ip,
            'snmp_disable' => 1,
            'os' => 'ping',
            'sysName' => $sysName,
            'type' => 'firewall',
        ]);

        try {
            $action = new ValidateDeviceAndCreate($newDevice, force: true);
            if (! $action->execute()) {
                Log::debug("next-hop $hop_ip: creation no-op (already exists)");

                return false;
            }
        } catch (\Throwable $e) {
            Log::warning("next-hop $hop_ip: creation failed: " . $e->getMessage());

            return false;
        }

        // Re-fetch the persisted Device so we have its device_id for parent linkage
        $persisted = Device::where('hostname', $hop_ip)->first();
        if ($persisted) {
            $this->ensureParentRelationship($persisted, $parent);
        }

        Eventlog::log(
            sprintf(
                "Next-hop discovery: added %s as pingonly child of %s (%s)",
                $hop_ip, $parent->hostname, $sysName
            ),
            $parent->device_id,
            'discovery',
            Severity::Notice
        );

        return true;
    }

    /**
     * Make sure $child's parents() includes $parent. Idempotent.
     *
     * @return bool true if a new attachment was created, false if already linked
     */
    private function ensureParentRelationship(Device $child, Device $parent): bool
    {
        if ($child->device_id === $parent->device_id) {
            return false;
        }
        if ($child->parents()->where('parent_device_id', $parent->device_id)->exists()) {
            return false;
        }
        $child->parents()->attach($parent->device_id);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling — pingonly children are polled by the standard ICMP path
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return null;
    }
}
