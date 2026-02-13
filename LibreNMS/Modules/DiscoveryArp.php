<?php

namespace LibreNMS\Modules;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Ipv4Mac;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IPv4;

class DiscoveryArp implements Module
{
    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['arp-table'];
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
        // Find all IPv4 addresses in the MAC table that haven't been discovered on monitored devices.
        $entries = Ipv4Mac::query()
            ->select(['ipv4_address', 'mac_address', 'port_id'])
            ->whereHas('port', fn ($query) => $query->isNotDeleted())
            ->whereDoesntHave('ipv4Address')
            ->orderBy('ipv4_address')
            ->get();

        $discoverable_names_ips = [];
        $ignored = [];
        $excluded = 0;
        $debounced = 0;

        foreach ($entries as $entry) {
            try {
                $ip = IPv4::parse($entry->ipv4_address);

                // Even though match_network is done inside discover_new_device, we do it here
                // as well in order to skip unnecessary reverse DNS lookups on discovered IPs.
                if ($ip->inNetworks(LibrenmsConfig::get('autodiscovery.nets-exclude'))) {
                    $excluded++;
                    continue;
                }

                if (! $ip->inNetworks(LibrenmsConfig::get('nets'))) {
                    $ignored[] = (string) $ip;
                    continue;
                }

                // Attempt discovery of each IP only once per run.
                if (! Cache::add('arp_discovery:' . $ip, true, 3600)) {
                    $debounced++;
                    continue;
                }

                $discoverable_names_ips[] = gethostbyaddr((string) $ip);
            } catch (InvalidIpException $e) {
                Log::debug('Invalid IP address encountered during ARP discovery: ' . $e->getMessage());
            }
        }

        $ignored_count = count($ignored);
        Log::info(sprintf('Found %d discoverable IPs, ignored %d, excluded %d, skipped (recent) %d', count($discoverable_names_ips), $ignored_count, $excluded, $debounced));

        // send a single eventlog per discovery with at most 5 IPs
        if ($ignored_count) {
            $ips = implode(',', array_slice($ignored, 0, 5));
            if ($ignored_count > 5) {
                $ips = '...';
            }
            Eventlog::log("ARP Discover: ignored $ignored_count IPs ($ips)", $os->getDeviceId(), 'discovery', Severity::Notice);
        }

        // Run device discovery on each of the devices we've detected so far.
        $device = $os->getDeviceArray();
        foreach ($discoverable_names_ips as $address) {
            discover_new_device($address, $device, 'ARP');
        }
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
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
        return null; // no testing for now
    }
}
