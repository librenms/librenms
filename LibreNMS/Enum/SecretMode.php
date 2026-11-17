<?php

namespace LibreNMS\Enum;

/**
 * How a polling method form selects its secret.
 */
enum SecretMode: string
{
    case Default = 'default'; // no secret, try the default credentials (new devices only)
    case Existing = 'existing'; // use secret_id
    case New = 'new'; // create a secret from description and secret_data
    case Edit = 'edit'; // update secret_id with description and secret_data
}
