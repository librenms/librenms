<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Location and version of external programs on this node
 *
 * @method static string path(string $binary)
 * @method static string|null find(string $binary)
 * @method static string rrdtoolVersion()
 * @method static string netSnmpVersion()
 * @method static void setPath(string $binary, ?string $path)
 * @method static void setVersion(string $binary, string $version)
 *
 * @see \LibreNMS\Util\Binaries
 */
class Binaries extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LibreNMS\Util\Binaries::class;
    }
}
