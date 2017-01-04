## Name:

**Dapphp\Radius** - A pure PHP RADIUS client based on the SysCo/al implementation 

## Version:

**2.5.0**

## Author:

Drew Phillips <drew@drew-phillips.com>
SysCo/al <developer@sysco.ch> (http://developer.sysco.ch/php/)

## Requirements:

* PHP 5.3 or greater

## Description:

**Dapphp\Radius** is a pure PHP RADIUS client for authenticating users against
a RADIUS server in PHP.  It currently supports basic RADIUS auth using PAP,
CHAP (MD5), MSCHAP v1, and EAP-MSCHAP v2.  The current 2.5.x branch is tested
to work with Microsoft Windows Server 2012 Network Policy Server and FreeRADIUS
2 and above and most likely works with other RADIUS server implementations.
PAP authentication has been tested on Microsoft Radius server IAS, Mideye
RADIUS Server, Radl, RSA SecurID, VASCO Middleware 3.0 server, WinRadius, and
ZyXEL ZyWALL OTP.

The PHP mcrypt extension is required if using MSCHAP v1 or v2.

## Installation:

The recommended way to install `dapphp/radius` is using [Composer](https://getcomposer.org).
If you are already using composer, simple run `composer require dapphp/radius` or add
`dapphp/radius` to your composer.json file's `require` section.

Standalone installation is also supported and a SPL autoloader is provided.
(Don't use the standalone autoloader if you're using Composer!).

To install standalone, download the release archive and extract to a location
on your server.  In your application, `require_once 'radius/autoload.php';` and
then you can use the class.

## Examples:

See the `examples/` directory for working examples (change the server address
and credentials to test).

## Synopsis:

<?php

use Dapphp\Radius\Radius;

require_once '/path/to/radius/autoload.php';
// or, if using composer
require_once '/path/to/vendor/autoload.php';

$client = new Radius();

// set server, secret, and basic attributes
$client->setServer('12.34.56.78') // RADIUS server address
       ->setSecret('radius shared secret')
       ->setNasIpAddress('10.0.1.2') // NAS server address
       ->setAttribute(32, 'login');  // NAS identifier
       
// PAP authentication; returns true if successful, false otherwise
$authenticated = $client->accessRequest($username, $password);

// CHAP-MD5 authentication
$client->setChapPassword($password); // set chap password
$authenticated = $client->accessRequest($username); // authenticate, don't specify pw here

// MSCHAP v1 authentication
$client->setMSChapPassword($password); // set ms chap password (uses mcrypt)
$authenticated = $client->accessRequest($username);

// EAP-MSCHAP v2 authentication
$authenticated = $client->accessRequestEapMsChapV2($username, $password);

if ($authenticated === false) {
    // false returned on failure
    echo sprintf(
        "Access-Request failed with error %d (%s).\n",
        $client->getErrorCode(),
        $client->getErrorMessage()
    );
} else {
    // access request was accepted - client authenticated successfully
    echo "Success!  Received Access-Accept response from RADIUS server.\n";
}

## Advanced Usage:

// Setting vendor specific attributes
// Many vendor IDs are available in \Dapphp\Radius\VendorId
// e.g. \Dapphp\Radius\VendorId::MICROSOFT
$client->setVendorSpecificAttribute($vendorId, $attributeNumber, $rawValue);

// Retrieving attributes from RADIUS responses after receiving a failure or success response
$value = $client->getAttribute($attributeId);

// Get an array of all received attributes
$attributes = getReceivedAttributes();

// Debugging
// Prior to sending a request, call
$client->setDebug(true); // enable debug output on console
// Shows what attributes are sent and received, and info about the request/response


## TODO:

- Set attributes by name, rather than number
- Vendor specific attribute dictionaries?
- Test with more implementations and confirm working
- Accounting?

## Copyright:

    Copyright (c) 2008, SysCo systemes de communication sa
    SysCo (tm) is a trademark of SysCo systemes de communication sa
    (http://www.sysco.ch/)
    All rights reserved.

    Copyright (c) 2016, Drew Phillips
    (https://drew-phillips.com)

    Pure PHP radius class is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public License as
    published by the Free Software Foundation, either version 3 of the License,
    or (at your option) any later version.

    Pure PHP radius class is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with Pure PHP radius class.
    If not, see <http://www.gnu.org/licenses/>
