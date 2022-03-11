<?php

$pkgs_id = [];
$pkgs_db_id = [];

// RPM
if (! empty($agent_data['rpm'])) {
    echo "\nRPM Packages: \n";
    // Build array of existing packages
    $manager = 'rpm';

    /** @var \Illuminate\Support\Collection $packages */
    $packages = DeviceCache::getPrimary()->packages->map(function(\App\Models\Package $package) {
        $package->status = 0;
        return $package;
    })->keyBy->getCompositeKey();

    foreach (explode("\n", $agent_data['rpm']) as $package) {
        [$name, $version, $build, $arch, $size] = explode(' ', $package);
        $package = new \App\Models\Package([
            'manager' => $manager,
            'name' => $name,
            'arch' => $arch,
            'version' => $version,
            'build' => $build,
            'size' => $size,
            'status' => 1,
        ]);
        $package_key = $package->getCompositeKey();
        if ($existing_package = $packages->get($package_key)) {
            $existing_package->fill($package->attributesToArray());
        } else {
            $packages->put($package_key, $package);
        }
    }
}//end if

// DPKG
if (! empty($agent_data['dpkg'])) {
    echo "\nDEB Packages: \n";
    // Build array of existing packages
    $manager = 'deb';

    /** @var \Illuminate\Support\Collection $db_packages */
    $packages = DeviceCache::getPrimary()->packages->map(function(\App\Models\Package $package) {
        $package->status = 0;
        return $package;
    })->keyBy->getCompositeKey();

    foreach (explode("\n", $agent_data['dpkg']) as $package) {
        [$name, $version, $arch, $size] = explode(' ', $package);
        $package = new \App\Models\Package([
            'manager' => $manager,
            'name' => $name,
            'arch' => $arch,
            'version' => $version,
            'build' => '',
            'size' => cast_number($size) * 1024,
            'status' => 1,
        ]);
        $package_key = $package->getCompositeKey();
        if ($existing_package = $packages->get($package_key)) {
            $existing_package->fill($package->attributesToArray());
        } else {
            $packages->put($package_key, $package);
        }
    }
}//end if

// update the database
if (isset($packages)) {
    DeviceCache::getPrimary()->packages()->saveMany($packages->where('status', 1));
    $packages->where('status', 0)->each->delete();
}

echo "\n";

unset($packages, $existing_package, $package);
