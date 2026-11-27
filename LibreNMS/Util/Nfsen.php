<?php

namespace LibreNMS\Util;

use App\Facades\LibrenmsConfig;

class Nfsen
{
    /**
     * Name NfSen uses for a host
     */
    public static function hostname(string $hostname): string
    {
        $split = LibrenmsConfig::get('nfsen_split_char');
        if ($split) {
            $hostname = str_replace('.', $split, $hostname);
        }

        $suffix = LibrenmsConfig::get('nfsen_suffix');

        return $suffix ? str_replace($suffix, '', $hostname) : $hostname;
    }

    /**
     * NfSen rrd directories, with trailing slash
     *
     * @return string[]
     */
    private static function rrdDirs(): array
    {
        return array_map(fn ($dir) => rtrim((string) $dir, '/') . '/', (array) LibrenmsConfig::get('nfsen_rrds', []));
    }

    /**
     * Path of the host rrd, if there is one
     */
    public static function rrd(string $hostname): ?string
    {
        foreach (self::rrdDirs() as $dir) {
            $file = $dir . self::hostname($hostname) . '.rrd';
            if (is_file($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Channel rrds of the host
     *
     * @return array<string, string> channel => rrd path
     */
    public static function channels(string $hostname): array
    {
        $channels = [];
        foreach (self::rrdDirs() as $dir) {
            foreach (glob($dir . self::hostname($hostname) . '/*.rrd') ?: [] as $file) {
                $channels[basename($file, '.rrd')] = $file;
            }
        }

        return $channels;
    }

    /**
     * Directory of the live nfcapd files of the host
     */
    public static function liveDir(string $hostname): ?string
    {
        foreach ((array) LibrenmsConfig::get('nfsen_base', []) as $dir) {
            if (is_dir($dir)) {
                return $dir . '/profiles-data/live/' . self::hostname($hostname);
            }
        }

        return null;
    }

    /**
     * Path of the nfcapd file holding the given time, relative to the live dir.
     * Layouts as defined by NfSen's SUBDIRLAYOUT.
     */
    public static function subPath(int $time): string
    {
        $time -= $time % 300;
        $dir = match ((int) LibrenmsConfig::get('nfsen_subdirlayout')) {
            1 => 'Y/m/d/',
            2 => 'Y/m/d/H/',
            3 => 'Y/W/w/',
            4 => 'Y/W/w/H/',
            5 => 'Y/z/',
            6 => 'Y/z/H/',
            7 => 'Y-m-d/',
            8 => 'Y-m-d/H/',
            default => '',
        };

        return date($dir, $time) . 'nfcapd.' . date('YmdHi', $time);
    }
}
