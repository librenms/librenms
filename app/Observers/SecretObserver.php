<?php

namespace App\Observers;

use App\Facades\LibrenmsConfig;
use App\Models\Secret;

class SecretObserver
{
    public function deleted(Secret $secret): void
    {
        // don't leave a deleted secret in the default credentials
        $defaultIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);
        $remainingIds = array_values(array_filter($defaultIds, fn ($id) => (int) $id !== $secret->id));

        if (count($remainingIds) !== count($defaultIds)) {
            LibrenmsConfig::persist('snmp.default_credentials', $remainingIds);
        }
    }
}
