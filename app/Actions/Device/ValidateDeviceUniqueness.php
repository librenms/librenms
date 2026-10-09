<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Exceptions\HostIpExistsException;
use LibreNMS\Exceptions\HostNameEmptyException;
use LibreNMS\Exceptions\HostnameExistsException;
use LibreNMS\Exceptions\HostSysnameExistsException;

class ValidateDeviceUniqueness
{
    /**
     * @throws HostNameEmptyException
     * @throws HostnameExistsException
     */
    public function validateHostname(string $hostname): void
    {
        if (empty($hostname)) {
            throw new HostNameEmptyException();
        }

        if (Device::where('hostname', $hostname)->exists()) {
            throw new HostnameExistsException($hostname);
        }
    }

    /**
     * @throws HostIpExistsException
     */
    public function validateIp(Device $device): void
    {
        if ($device->overwrite_ip) {
            $ip = $device->overwrite_ip;
        } elseif (LibrenmsConfig::get('addhost_alwayscheckip')) {
            $ip = gethostbyname($device->hostname);
        } else {
            $ip = $device->hostname;
        }

        $existing = Device::findByIp($ip);

        if ($existing) {
            throw new HostIpExistsException($device->hostname, $existing->hostname, $ip);
        }
    }

    /**
     * @throws HostSysnameExistsException
     */
    public function validateSysName(Device $device): void
    {
        if (LibrenmsConfig::get('allow_duplicate_sysName') || empty($device->sysName)) {
            return;
        }

        if (Device::where('sysName', $device->sysName)
            ->when(LibrenmsConfig::get('mydomain'), function ($query, $domain) use ($device): void {
                $query->orWhere('sysName', rtrim((string) $device->sysName, '.') . '.' . $domain);
            })->exists()) {
            throw new HostSysnameExistsException($device->hostname, $device->sysName);
        }
    }
}
