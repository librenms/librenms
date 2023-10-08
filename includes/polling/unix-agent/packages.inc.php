<?php

use App\Models\Package;

$pkgs_id = [];
$pkgs_db_id = [];

$managers = [
    'rpm' => [
        'name' => 'RPM',
        'process' => function ($line) {
            [$name, $version, $build, $arch, $size] = explode(' ', $line);

            return new Package([
                'manager' => 'rpm',
                'name' => $name,
                'arch' => $arch,
                'version' => $version,
                'build' => $build,
                'size' => $size,
                'status' => 1,
            ]);
        },
    ],
    'dpkg' => [
        'name' => 'DEB',
        'process' => function ($line) {
            [$name, $version, $arch, $size] = explode(' ', $line);

            return new Package([
                'manager' => 'deb',
                'name' => $name,
                'arch' => $arch,
                'version' => $version,
                'build' => '',
                'size' => cast_number($size) * 1024,
                'status' => 1,
            ]);
        },
    ],
    'pacman' => [
        'name' => 'Pacman',
        'process' => function ($line) {
            [$name, $version, $arch, $size] = explode(' ', $line);

            return new Package([
                'manager' => 'pacman',
                'name' => $name,
                'arch' => $arch,
                'version' => $version,
                'build' => '',
                'size' => (int) \LibreNMS\Util\Number::toBytes($size),
                'status' => 1,
            ]);
        },
    ],
];

foreach ($managers as $key => $manager) {
    if (! empty($agent_data[$key])) {
        echo "\n{$manager['name']} Packages: \n";

        /** @var \Illuminate\Support\Collection $packages */
        $packages = DeviceCache::getPrimary()->packages->map(function (Package $package) {
            $package->status = 0;

            return $package;
        })->keyBy->getCompositeKey();

        foreach (explode("\n", trim($agent_data[$key])) as $line) {
            /** @var \App\Models\Package $package */
            $package = $manager['process']($line);

            if (! $package->isValid()) {
                continue; // failed to parse
            }

            $package_key = $package->getCompositeKey();
            if ($existing_package = $packages->get($package_key)) {
                $existing_package->fill($package->attributesToArray());
            } else {
                $packages->put($package_key, $package);
            }
        }

        break;
    }
}

// update the database
if (isset($packages)) {
    DeviceCache::getPrimary()->packages()->saveMany($packages->where('status', 1));
    $packages->where('status', 0)->each->delete();
}

echo "\n";

unset($packages, $existing_package, $package, $managers);
