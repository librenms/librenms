<?php

namespace LibreNMS\Enum;

/**
 * How the MTU check will handle the need for fragmenting packets
 */
enum MtuFragOption: string
{
    case Default = 'default'; // Use system settings to determine whether fragmentation is allowed
    case PMTU = 'pmtu'; // Allow fragmentation using PMTU discovery
    case Allow = 'allow'; // Allow routers to fragment and transmit packets
    case Deny = 'deny'; // Do not allow fragmentation
}
