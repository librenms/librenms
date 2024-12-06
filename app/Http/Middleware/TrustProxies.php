<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    protected function getTrustedHeaderNames()
    {
        $this->headers = config('trustedproxy.headers');

        return parent::getTrustedHeaderNames();
    }

    /**
     * Get the trusted proxies.
     *
     * @return array|string|null
     */
    protected function proxies()
    {
        $this->proxies = config('trustedproxy.proxies');

        return parent::proxies();
    }
}
