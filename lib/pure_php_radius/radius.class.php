<?php

/*********************************************************************
 *
 * Pure PHP radius class
 *
 * This Radius class is a radius client implementation in pure PHP
 * following the RFC 2865 rules (http://www.ietf.org/rfc/rfc2865.txt)
 *
 * This class works with at least the following RADIUS servers:
 *  - Authenex Strong Authentication System (ASAS) with two-factor authentication
 *  - FreeRADIUS, a free Radius server implementation for Linux and *nix environments
 *  - Microsoft Radius server IAS
 *  - Mideye RADIUS server (http://www.mideye.com)
 *  - Radl, a free Radius server for Windows
 *  - RSA SecurID
 *  - VASCO Middleware 3.0 server
 *  - WinRadius, Windows Radius server (free for 5 users)
 *  - ZyXEL ZyWALL OTP (Authenex ASAS branded by ZyXEL, cheaper)
 *
 *
 * LICENCE
 *
 *   Copyright (c) 2008, SysCo systemes de communication sa
 *   SysCo (tm) is a trademark of SysCo systemes de communication sa
 *   (http://www.sysco.ch/)
 *   All rights reserved.
 * 
 *   This file is part of the Pure PHP radius class
 *
 *   Pure PHP radius class is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public License as
 *   published by the Free Software Foundation, either version 3 of the License,
 *   or (at your option) any later version.
 * 
 *   Pure PHP radius class is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Lesser General Public License for more details.
 * 
 *   You should have received a copy of the GNU Lesser General Public
 *   License along with Pure PHP radius class.
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @author: SysCo/al
 * @since CreationDate: 2008-01-04
 * @copyright (c) 2008 by SysCo systemes de communication sa
 * @version $LastChangedRevision: 1.2.2 $
 * @version $LastChangedDate: 2009-01-05 $
 * @version $LastChangedBy: SysCo/al $
 * @link $HeadURL: radius.class.php $
 * @link http://developer.sysco.ch/php/
 * @link developer@sysco.ch
 * Language: PHP 4.0.7 or higher
 *
 *
 * Usage
 *
 *   require_once('radius.class.php');
 *   $radius = new Radius($ip_radius_server = 'radius_server_ip_address', $shared_secret = 'radius_shared_secret'[, $radius_suffix = 'optional_radius_suffix'[, $udp_timeout = udp_timeout_in_seconds[, $authentication_port = 1812]]]);
 *   $result = $radius->Access_Request($username = 'username', $password = 'password'[, $udp_timeout = udp_timeout_in_seconds]);
 *
 *
 * Examples
 *
 *   Example 1
 *     <?php
 *         require_once('radius.class.php');
 *         $radius = new Radius('127.0.0.1', 'secret');
 *         $radius->SetNasIpAddress('1.2.3.4'); // Needed for some devices, and not auto_detected if PHP not runned through a web server
 *         if ($radius->AccessRequest('user', 'pass'))
 *         {
 *             echo "Authentication accepted.";
 *         }
 *         else
 *         {
 *             echo "Authentication rejected.";
 *         }
 *     ?>
 *
 *   Example 2
 *     <?php
 *         require_once('radius.class.php');
 *         $radius = new Radius('127.0.0.1', 'secret');
 *         $radius->SetNasPort(0);
 *         $radius->SetNasIpAddress('1.2.3.4'); // Needed for some devices, and not auto_detected if PHP not runned through a web server
 *         if ($radius->AccessRequest('user', 'pass'))
 *         {
 *             echo "Authentication accepted.";
 *             echo "<br />";
 *         }
 *         else
 *         {
 *             echo "Authentication rejected.";
 *             echo "<br />";
 *         }
 *         echo $radius->GetReadableReceivedAttributes();
 *     ?>
 *
 *
 * External file needed
 *
 *   none.
 *
 *
 * External file created
 *
 *   none.
 *
 *
 * Special issues
 *
 *   - Sockets support must be enabled.
 *     * In Linux and *nix environments, the extension is enabled at
 *       compile time using the --enable-sockets configure option
 *     * In Windows, PHP Sockets can be activated by un-commenting
 *       extension=php_sockets.dll in php.ini
 *
 *
 * Other related ressources
 *
 *   FreeRADIUS, a free Radius server implementation for Linux and *nix environments:
 *     http://www.freeradius.org/
 *
 *   WinRadius, Windows Radius server (free for 5 users):
 *     http://www.itconsult2000.com/en/product/WinRadius.zip
 *
 *   Radl, a free Radius server for Windows:
 *     http://www.loriotpro.com/Products/RadiusServer/FreeRadiusServer_EN.php
 *
 *   DOS command line Radius client:
 *     http://www.itconsult2000.com/en/product/WinRadiusClient.zip
 *
 *
 * Users feedbacks and comments
 *
 * 2008-07-02 Pim Koeman/Parantion
 *
 *   When using a radius connection behind a linux iptables firewall
 * 	 allow port 1812 and 1813 with udp protocol
 *
 *   IPTABLES EXAMPLE (command line):
 *   iptables -A AlwaysACCEPT -p udp --dport 1812 -j ACCEPT
 *   iptables -A AlwaysACCEPT -p udp --dport 1813 -j ACCEPT
 *
 *   or put the lines in /etc/sysconfig/iptables (red-hat type systems (fedora, centos, rhel etc.)
 *   -A AlwaysACCEPT -p udp --dport 1812 -j ACCEPT
 *   -A AlwaysACCEPT -p udp --dport 1813 -j ACCEPT
 *
 *
 * Change Log
 *
 *   2009-01-05 1.2.2 SysCo/al Added Robert Svensson feedback, Mideye RADIUS server is supported
 *   2008-11-11 1.2.1 SysCo/al Added Carlo Ferrari resolution in examples (add NAS IP Address for a VASCO Middleware server)
 *   2008-07-07 1.2   SysCo/al Added Pim Koeman (Parantion) contribution
 *                              - comments concerning using radius behind a linux iptables firewall
 *                             Added Jon Bright (tick Trading Software AG) contribution
 *                              - false octal encoding with 0xx indexes (indexes are now rewritten in xx only)
 *                              - challenge/response support for the RSA SecurID New-PIN mode
 *                             Added GetRadiusPacketInfo() method
 *                             Added GetAttributesInfo() method
 *                             Added DecodeVendorSpecificContent() (to answer Raul Carvalho's question)
 *                             Added Decoded Vendor Specific Content in debug messages
 *   2008-02-04 1.1   SysCo/al Typo error for the udp_timeout parameter (line 256 in the version 1.0)
 *   2008-01-07 1.0   SysCo/al Initial release
 *
 *********************************************************************/


/*********************************************************************
 *
 * Radius
 * Pure PHP radius class
 *
 * Creation 2008-01-04
 * Update 2009-01-05
 * @package radius
 * @version v.1.2.2
 * @author SysCo/al
 *
 *********************************************************************/
class Radius
{
    var $_ip_radius_server;       // Radius server IP address
    var $_shared_secret;          // Shared secret with the radius server
    var $_radius_suffix;          // Radius suffix (default is '');
    var $_udp_timeout;            // Timeout of the UDP connection in seconds (default value is 5)
    var $_authentication_port;    // Authentication port (default value is 1812)
    var $_accounting_port;        // Accouting port (default value is 1813)
    var $_nas_ip_address;         // NAS IP address
    var $_nas_port;               // NAS port
    var $_encrypted_password;     // Encrypted password, as described in the RFC 2865
    var $_user_ip_address;        // Remote IP address of the user
    var $_request_authenticator;  // Request-Authenticator, 16 octets random number
    var $_response_authenticator; // Request-Authenticator, 16 octets random number
    var $_username;               // Username to sent to the Radius server
    var $_password;               // Password to sent to the Radius server (clear password, must be encrypted)
    var $_identifier_to_send;     // Identifier field for the packet to be sent
    var $_identifier_received;    // Identifier field for the received packet
    var $_radius_packet_to_send;  // Radius packet code (1=Access-Request, 2=Access-Accept, 3=Access-Reject, 4=Accounting-Request, 5=Accounting-Response, 11=Access-Challenge, 12=Status-Server (experimental), 13=Status-Client (experimental), 255=Reserved
    var $_radius_packet_received; // Radius packet code (1=Access-Request, 2=Access-Accept, 3=Access-Reject, 4=Accounting-Request, 5=Accounting-Response, 11=Access-Challenge, 12=Status-Server (experimental), 13=Status-Client (experimental), 255=Reserved
    var $_attributes_to_send;     // Radius attributes to send
    var $_attributes_received;    // Radius attributes received
    var $_socket_to_server;       // Socket connection
    var $_debug_mode;             // Debug mode flag
    var $_attributes_info;        // Attributes info array
    var $_radius_packet_info;     // Radius packet codes info array
    var $_last_error_code;        // Last error code
    var $_last_error_message;     // Last error message
    

    /*********************************************************************
     *
     * Name: Radius
     * short description: Radius class constructor
     *
     * Creation 2008-01-04
     * Update 2009-01-05
     * @version v.1.2.2
     * @author SysCo/al
     * @param string ip address of the radius server
     * @param string shared secret with the radius server
     * @param string radius domain name suffix (default is empty)
     * @param integer UDP timeout (default is 5)
     * @param integer authentication port
     * @param integer accounting port
     * @return NULL
     *********************************************************************/
    public function Radius($ip_radius_server = '127.0.0.1', $shared_secret = '', $radius_suffix = '', $udp_timeout = 5, $authentication_port = 1812, $accounting_port = 1813)
    {
        $this->_radius_packet_info[1] = 'Access-Request';
        $this->_radius_packet_info[2] = 'Access-Accept';
        $this->_radius_packet_info[3] = 'Access-Reject';
        $this->_radius_packet_info[4] = 'Accounting-Request';
        $this->_radius_packet_info[5] = 'Accounting-Response';
        $this->_radius_packet_info[11] = 'Access-Challenge';
        $this->_radius_packet_info[12] = 'Status-Server (experimental)';
        $this->_radius_packet_info[13] = 'Status-Client (experimental)';
        $this->_radius_packet_info[255] = 'Reserved';
        
        $this->_attributes_info[1] = array('User-Name', 'S');
        $this->_attributes_info[2] = array('User-Password', 'S');
        $this->_attributes_info[3] = array('CHAP-Password', 'S'); // Type (1) / Length (1) / CHAP Ident (1) / String
        $this->_attributes_info[4] = array('NAS-IP-Address', 'A');
        $this->_attributes_info[5] = array('NAS-Port', 'I');
        $this->_attributes_info[6] = array('Service-Type', 'I');
        $this->_attributes_info[7] = array('Framed-Protocol', 'I');
        $this->_attributes_info[8] = array('Framed-IP-Address', 'A');
        $this->_attributes_info[9] = array('Framed-IP-Netmask', 'A');
        $this->_attributes_info[10] = array('Framed-Routing', 'I');
        $this->_attributes_info[11] = array('Filter-Id', 'T');
        $this->_attributes_info[12] = array('Framed-MTU', 'I');
        $this->_attributes_info[13] = array('Framed-Compression', 'I');
        $this->_attributes_info[14] = array( 'Login-IP-Host', 'A');
        $this->_attributes_info[15] = array('Login-service', 'I');
        $this->_attributes_info[16] = array('Login-TCP-Port', 'I');
        $this->_attributes_info[17] = array('(unassigned)', '');
        $this->_attributes_info[18] = array('Reply-Message', 'T');
        $this->_attributes_info[19] = array('Callback-Number', 'S');
        $this->_attributes_info[20] = array('Callback-Id', 'S');
        $this->_attributes_info[21] = array('(unassigned)', '');
        $this->_attributes_info[22] = array('Framed-Route', 'T');
        $this->_attributes_info[23] = array('Framed-IPX-Network', 'I');
        $this->_attributes_info[24] = array('State', 'S');
        $this->_attributes_info[25] = array('Class', 'S');
        $this->_attributes_info[26] = array('Vendor-Specific', 'S'); // Type (1) / Length (1) / Vendor-Id (4) / Vendor type (1) / Vendor length (1) / Attribute-Specific...
        $this->_attributes_info[27] = array('Session-Timeout', 'I');
        $this->_attributes_info[28] = array('Idle-Timeout', 'I');
        $this->_attributes_info[29] = array('Termination-Action', 'I');
        $this->_attributes_info[30] = array('Called-Station-Id', 'S');
        $this->_attributes_info[31] = array('Calling-Station-Id', 'S');
        $this->_attributes_info[32] = array('NAS-Identifier', 'S');
        $this->_attributes_info[33] = array('Proxy-State', 'S');
        $this->_attributes_info[34] = array('Login-LAT-Service', 'S');
        $this->_attributes_info[35] = array('Login-LAT-Node', 'S');
        $this->_attributes_info[36] = array('Login-LAT-Group', 'S');
        $this->_attributes_info[37] = array('Framed-AppleTalk-Link', 'I');
        $this->_attributes_info[38] = array('Framed-AppleTalk-Network', 'I');
        $this->_attributes_info[39] = array('Framed-AppleTalk-Zone', 'S');
        $this->_attributes_info[60] = array('CHAP-Challenge', 'S');
        $this->_attributes_info[61] = array('NAS-Port-Type', 'I');
        $this->_attributes_info[62] = array('Port-Limit', 'I');
        $this->_attributes_info[63] = array('Login-LAT-Port', 'S');
        $this->_attributes_info[76] = array('Prompt', 'I');

        $this->_identifier_to_send = 0;
        $this->_user_ip_address = (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'0.0.0.0');
        
        $this->GenerateRequestAuthenticator();
        $this->SetIpRadiusServer($ip_radius_server);
        $this->SetSharedSecret($shared_secret);
        $this->SetAuthenticationPort($authentication_port);
        $this->SetAccountingPort($accounting_port);
        $this->SetRadiusSuffix($radius_suffix);
        $this->SetUdpTimeout($udp_timeout);
        $this->SetUsername();
        $this->SetPassword();
        $this->SetNasIpAddress();
        $this->SetNasPort();
        
        $this->ClearLastError();
        $this->ClearDataToSend();
        $this->ClearDataReceived();
    }


    function GetNextIdentifier()
    {
        $this->_identifier_to_send = (($this->_identifier_to_send + 1) % 256);
        return $this->_identifier_to_send;
    }
    

    function GenerateRequestAuthenticator()
    {
        $this->_request_authenticator = '';
        for ($ra_loop = 0; $ra_loop <= 15; $ra_loop++)
        {
            $this->_request_authenticator .= chr(rand(1, 255));
        }
    }


    function GetRequestAuthenticator()
    {
        return $this->_request_authenticator;
    }


    function GetLastError()
    {
        if (0 < $this->_last_error_code)
        {
            return $this->_last_error_message.' ('.$this->_last_error_code.')';
        }
        else
        {
            return '';
        }
    }
    
    
    function ClearDataToSend()
    {
        $this->_radius_packet_to_send = 0;
        $this->_attributes_to_send = NULL;
    }
    
    
    function ClearDataReceived()
    {
        $this->_radius_packet_received = 0;
        $this->_attributes_received = NULL;
    }
    

    function SetPacketCodeToSend($packet_code)
    {
        $this->_radius_packet_to_send = $packet_code;
    }
    
    
    function SetDebugMode($debug_mode)
    {
        $this->_debug_mode = (TRUE === $debug_mode);
    }
    
    
    function SetIpRadiusServer($ip_radius_server)
    {
        $this->_ip_radius_server = gethostbyname($ip_radius_server);
    }
    
    
    function SetSharedSecret($shared_secret)
    {
        $this->_shared_secret = $shared_secret;
    }
    
    
    function SetRadiusSuffix($radius_suffix)
    {
        $this->_radius_suffix = $radius_suffix;
    }
    
    
    function SetUsername($username = '')
    {
        $temp_username = $username;
        if (false === strpos($temp_username, '@'))
        {
            $temp_username .= $this->_radius_suffix;
        }
        
        $this->_username = $temp_username;
        $this->SetAttribute(1, $this->_username);
    }
    
    
    function SetPassword($password = '')
    {
        $this->_password = $password;
        $encrypted_password = '';
        $padded_password = $password;
        
        if (0 != (strlen($password)%16))
        {
            $padded_password .= str_repeat(chr(0),(16-strlen($password)%16));
        }
        
        $previous_result = $this->_request_authenticator;
        
        for ($full_loop = 0; $full_loop < (strlen($padded_password)/16); $full_loop++)
        {
            $xor_value = md5($this->_shared_secret.$previous_result);
            
            $previous_result = '';
            for ($xor_loop = 0; $xor_loop <= 15; $xor_loop++)
            {
                $value1 = ord(substr($padded_password, ($full_loop * 16) + $xor_loop, 1));
                $value2 = hexdec(substr($xor_value, 2*$xor_loop, 2));
                $xor_result = $value1 ^ $value2;
                $previous_result .= chr($xor_result);
            }
            $encrypted_password .= $previous_result;
        }
        
        $this->_encrypted_password = $encrypted_password;
        $this->SetAttribute(2, $this->_encrypted_password);
    }


    function SetNasIPAddress($nas_ip_address = '')
    {
        if (0 < strlen($nas_ip_address))
        {
            $this->_nas_ip_address = gethostbyname($nas_ip_address);
        }
        else
        {
            $this->_nas_ip_address = gethostbyname(isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:'0.0.0.0');
        }
        $this->SetAttribute(4, $this->_nas_ip_address);
    }
    
    
    function SetNasPort($nas_port = 0)
    {
        $this->_nas_port = intval($nas_port);
        $this->SetAttribute(5, $this->_nas_port);
    }
    
    
    function SetUdpTimeout($udp_timeout = 5)
    {
        if (intval($udp_timeout) > 0)
        {
            $this->_udp_timeout = intval($udp_timeout);
        }
    }
    
    
    function ClearLastError()
    {
        $this->_last_error_code    = 0;
        $this->_last_error_message = '';
    }
    
    
    function SetAuthenticationPort($authentication_port)
    {
        if ((intval($authentication_port) > 0) && (intval($authentication_port) < 65536))
        {
            $this->_authentication_port = intval($authentication_port);
        }
    }
    
    
    function SetAccountingPort($accounting_port)
    {
        if ((intval($accounting_port) > 0) && (intval($accounting_port) < 65536))
        {
            $this->_accounting_port = intval($accounting_port);
        }
    }
    
    
    function GetReceivedPacket()
    {
        return $this->_radius_packet_received;
    }


    function GetReceivedAttributes()
    {
        return $this->_attributes_received;
    }
    

    function GetReadableReceivedAttributes()
    {
        $readable_attributes = '';
        if (isset($this->_attributes_received))
        {
            foreach($this->_attributes_received as $one_received_attribute)
            {
                $attributes_info = $this->GetAttributesInfo($one_received_attribute[0]);
                $readable_attributes .= $attributes_info[0].": ";
                if (26 == $one_received_attribute[0])
                {
                    $vendor_array = $this->DecodeVendorSpecificContent($one_received_attribute[1]);
                    foreach($vendor_array as $vendor_one)
                    {
                        $readable_attributes .= 'Vendor-Id: '.$vendor_one[0].", Vendor-type: ".$vendor_one[1].",  Attribute-specific: ".$vendor_one[2];
                    }
                }
                else
                {
                    $readable_attributes .= $one_received_attribute[1];
                }
                $readable_attributes .= "<br />\n";
            }
        }
        return $readable_attributes;
    }
    

    function GetAttribute($attribute_type)
    {
        $attribute_value = NULL;
        foreach($this->_attributes_received as $one_received_attribute)
        {
            if (intval($attribute_type) == $one_received_attribute[0])
            {
                $attribute_value = $one_received_attribute[1];
                break;
            }
        }
        return $attribute_value;
    }


    function GetRadiusPacketInfo($info_index)
    {
        if (isset($this->_radius_packet_info[intval($info_index)]))
        {
            return $this->_radius_packet_info[intval($info_index)];
        }
        else
        {
            return '';
        }
    }


    function GetAttributesInfo($info_index)
    {
        if (isset($this->_attributes_info[intval($info_index)]))
        {
            return $this->_attributes_info[intval($info_index)];
        }
        else
        {
            return array('','');
        }
    }


    function DebugInfo($debug_info)
    {
        if ($this->_debug_mode)
        {
            echo date('Y-m-d H:i:s').' DEBUG: ';
            echo $debug_info;
            echo '<br />';
            flush();
        }
    }
    
    
    function SetAttribute($type, $value)
    {
        $attribute_index = -1;
        for ($attributes_loop = 0; $attributes_loop < count($this->_attributes_to_send); $attributes_loop++)
        {
            if ($type == ord(substr($this->_attributes_to_send[$attributes_loop], 0, 1)))
            {
                $attribute_index = $attributes_loop;
                break;
            }
        }

        $temp_attribute = NULL;
        
        if (isset($this->_attributes_info[$type]))
        {
            switch ($this->_attributes_info[$type][1])
            {
                case 'T': // Text, 1-253 octets containing UTF-8 encoded ISO 10646 characters (RFC 2279).
                    $temp_attribute = chr($type).chr(2+strlen($value)).$value;
                    break;
                case 'S': // String, 1-253 octets containing binary data (values 0 through 255 decimal, inclusive).
                    $temp_attribute = chr($type).chr(2+strlen($value)).$value;
                    break;
                case 'A': // Address, 32 bit value, most significant octet first.
                    $ip_array = explode(".", $value);
                    $temp_attribute = chr($type).chr(6).chr($ip_array[0]).chr($ip_array[1]).chr($ip_array[2]).chr($ip_array[3]);
                    break;
                case 'I': // Integer, 32 bit unsigned value, most significant octet first.
                    $temp_attribute = chr($type).chr(6).chr(($value/(256*256*256))%256).chr(($value/(256*256))%256).chr(($value/(256))%256).chr($value%256);
                    break;
                case 'D': // Time, 32 bit unsigned value, most significant octet first -- seconds since 00:00:00 UTC, January 1, 1970. (not used in this RFC)
                    $temp_attribute = NULL;
                    break;
                default:
                    $temp_attribute = NULL;
            }
        }
                    
        if ($attribute_index > -1)
        {
            $this->_attributes_to_send[$attribute_index] = $temp_attribute;
            $additional_debug = 'Modified';
        }
        else
        {
            $this->_attributes_to_send[] = $temp_attribute;
            $additional_debug = 'Added';
        }
        $attribute_info = $this->GetAttributesInfo($type);
        $this->DebugInfo($additional_debug.' Attribute '.$type.' ('.$attribute_info[0].'), format '.$attribute_info[1].', value <em>'.$value.'</em>');
    }


    function DecodeAttribute($attribute_raw_value, $attribute_format)
    {
        $attribute_value = NULL;
        
        if (isset($this->_attributes_info[$attribute_format]))
        {
            switch ($this->_attributes_info[$attribute_format][1])
            {
                case 'T': // Text, 1-253 octets containing UTF-8 encoded ISO 10646 characters (RFC 2279).
                    $attribute_value = $attribute_raw_value;
                    break;
                case 'S': // String, 1-253 octets containing binary data (values 0 through 255 decimal, inclusive).
                    $attribute_value = $attribute_raw_value;
                    break;
                case 'A': // Address, 32 bit value, most significant octet first.
                    $attribute_value = ord(substr($attribute_raw_value, 0, 1)).'.'.ord(substr($attribute_raw_value, 1, 1)).'.'.ord(substr($attribute_raw_value, 2, 1)).'.'.ord(substr($attribute_raw_value, 3, 1));
                    break;
                case 'I': // Integer, 32 bit unsigned value, most significant octet first.
                    $attribute_value = (ord(substr($attribute_raw_value, 0, 1))*256*256*256)+(ord(substr($attribute_raw_value, 1, 1))*256*256)+(ord(substr($attribute_raw_value, 2, 1))*256)+ord(substr($attribute_raw_value, 3, 1));
                    break;
                case 'D': // Time, 32 bit unsigned value, most significant octet first -- seconds since 00:00:00 UTC, January 1, 1970. (not used in this RFC)
                    $attribute_value = NULL;
                    break;
                default:
                    $attribute_value = NULL;
            }
        }
        return $attribute_value;
    }


    /*********************************************************************
     * Array returned: array(array(Vendor-Id1, Vendor type1, Attribute-Specific1), ..., array(Vendor-IdN, Vendor typeN, Attribute-SpecificN)
     *********************************************************************/
    function DecodeVendorSpecificContent($vendor_specific_raw_value)
    {
        $result = array();
        $offset_in_raw = 0;
        $vendor_id = (ord(substr($vendor_specific_raw_value, 0, 1))*256*256*256)+(ord(substr($vendor_specific_raw_value, 1, 1))*256*256)+(ord(substr($vendor_specific_raw_value, 2, 1))*256)+ord(substr($vendor_specific_raw_value, 3, 1));
        $offset_in_raw += 4;
        while ($offset_in_raw < strlen($vendor_specific_raw_value))
        {
            $vendor_type = (ord(substr($vendor_specific_raw_value, 0+$offset_in_raw, 1)));
            $vendor_length = (ord(substr($vendor_specific_raw_value, 1+$offset_in_raw, 1)));
            $attribute_specific = substr($vendor_specific_raw_value, 2+$offset_in_raw, $vendor_length);
            $result[] = array($vendor_id, $vendor_type, $attribute_specific);
            $offset_in_raw += ($vendor_length);
        }
        
        return $result;
    }


    /*
     * Function : AccessRequest
     *
     * Return TRUE if Access-Request is accepted, FALSE otherwise
     */
    function AccessRequest($username = '', $password = '', $udp_timeout = 0, $state = NULL)
    {
        $this->ClearDataReceived();
        $this->ClearLastError();
        
        $this->SetPacketCodeToSend(1); // Access-Request
        
        if (0 < strlen($username))
        {
            $this->SetUsername($username);
        }
        
        if (0 < strlen($password))
        {
            $this->SetPassword($password);
        }

        if ($state!==NULL)
        {
            $this->SetAttribute(24, $state);
        }
        else
        {
            $this->SetAttribute(6, 1); // 1=Login
        }

        if (intval($udp_timeout) > 0)
        {
            $this->SetUdpTimeout($udp_timeout);
        }

        $attributes_content = '';
        for ($attributes_loop = 0; $attributes_loop < count($this->_attributes_to_send); $attributes_loop++)
        {
            $attributes_content .= $this->_attributes_to_send[$attributes_loop];
        }

        $packet_length  = 4; // Radius packet code + Identifier + Length high + Length low
        $packet_length += strlen($this->_request_authenticator); // Request-Authenticator
        $packet_length += strlen($attributes_content); // Attributes
        
        $packet_data  = chr($this->_radius_packet_to_send);
        $packet_data .= chr($this->GetNextIdentifier());
        $packet_data .= chr(intval($packet_length/256));
        $packet_data .= chr(intval($packet_length%256));
        $packet_data .= $this->_request_authenticator;
        $packet_data .= $attributes_content;

        $_socket_to_server = socket_create(AF_INET, SOCK_DGRAM, 17); // UDP packet = 17
        
        if ($_socket_to_server === FALSE)
        {
            $this->_last_error_code    = socket_last_error();
            $this->_last_error_message = socket_strerror($this->_last_error_code);
        }
        elseif (FALSE === socket_connect($_socket_to_server, $this->_ip_radius_server, $this->_authentication_port))
        {
            $this->_last_error_code    = socket_last_error();
            $this->_last_error_message = socket_strerror($this->_last_error_code);
        }
        elseif (FALSE === socket_write($_socket_to_server, $packet_data, $packet_length))
        {
            $this->_last_error_code    = socket_last_error();
            $this->_last_error_message = socket_strerror($this->_last_error_code);
        }
        else
        {
            $this->DebugInfo('<b>Packet type '.$this->_radius_packet_to_send.' ('.$this->GetRadiusPacketInfo($this->_radius_packet_to_send).')'.' sent</b>');
            if ($this->_debug_mode)
            {
                $readable_attributes = '';
                foreach($this->_attributes_to_send as $one_attribute_to_send)
                {
                    $attribute_info = $this->GetAttributesInfo(ord(substr($one_attribute_to_send,0,1)));
                    $this->DebugInfo('Attribute '.ord(substr($one_attribute_to_send,0,1)).' ('.$attribute_info[0].'), length '.(ord(substr($one_attribute_to_send,1,1))-2).', format '.$attribute_info[1].', value <em>'.$this->DecodeAttribute(substr($one_attribute_to_send,2), ord(substr($one_attribute_to_send,0,1))).'</em>');
                }
            }
            $read_socket_array   = array($_socket_to_server);
            $write_socket_array  = NULL;
            $except_socket_array = NULL;

            $received_packet = chr(0);

            if (!(FALSE === socket_select($read_socket_array, $write_socket_array, $except_socket_array, $this->_udp_timeout)))
            {
                if (in_array($_socket_to_server, $read_socket_array))
                {
                    if (FALSE === ($received_packet = @socket_read($_socket_to_server, 1024))) // @ used, than no error is displayed if the connection is closed by the remote host
                    {
                        $received_packet = chr(0);
                        $this->_last_error_code    = socket_last_error();
                        $this->_last_error_message = socket_strerror($this->_last_error_code);
                    }
                    else
                    {
                        socket_close($_socket_to_server);
                    }
                }
            }
            else
            {
                socket_close($_socket_to_server);
            }
        }

        $this->_radius_packet_received = intval(ord(substr($received_packet, 0, 1)));
        
        $this->DebugInfo('<b>Packet type '.$this->_radius_packet_received.' ('.$this->GetRadiusPacketInfo($this->_radius_packet_received).')'.' received</b>');
        
        if ($this->_radius_packet_received > 0)
        {
            $this->_identifier_received = intval(ord(substr($received_packet, 1, 1)));
            $packet_length = (intval(ord(substr($received_packet, 2, 1))) * 256) + (intval(ord(substr($received_packet, 3, 1))));
            $this->_response_authenticator = substr($received_packet, 4, 16);
            $attributes_content = substr($received_packet, 20, ($packet_length - 4 - 16));
            while (strlen($attributes_content) > 2)
            {
                $attribute_type = intval(ord(substr($attributes_content,0,1)));
                $attribute_length = intval(ord(substr($attributes_content,1,1)));
                $attribute_raw_value = substr($attributes_content,2,$attribute_length-2);
                $attributes_content = substr($attributes_content, $attribute_length);

                $attribute_value = $this->DecodeAttribute($attribute_raw_value, $attribute_type);

                $attribute_info = $this->GetAttributesInfo($attribute_type);
                if (26 == $attribute_type)
                {
                    $vendor_array = $this->DecodeVendorSpecificContent($attribute_value);
                    foreach($vendor_array as $vendor_one)
                    {
                        $this->DebugInfo('Attribute '.$attribute_type.' ('.$attribute_info[0].'), length '.($attribute_length-2).', format '.$attribute_info[1].', Vendor-Id: '.$vendor_one[0].", Vendor-type: ".$vendor_one[1].",  Attribute-specific: ".$vendor_one[2]);
                    }
                }
                else
                {
                    $this->DebugInfo('Attribute '.$attribute_type.' ('.$attribute_info[0].'), length '.($attribute_length-2).', format '.$attribute_info[1].', value <em>'.$attribute_value.'</em>');
                }

                $this->_attributes_received[] = array($attribute_type, $attribute_value);
            }
        }
        
        return (2 == ($this->_radius_packet_received));
    }
}

?>
