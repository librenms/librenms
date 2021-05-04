<?php
/**
 * ActiveDirectoryCommonirectoryCommon.php
 *
 * Common code from AD auth modules
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Authentication;

use LibreNMS\Config;

trait ActiveDirectoryCommon
{
    protected function getUseridFromSid($sid)
    {
        return preg_replace('/.*-(\d+)$/', '$1', $sid);
    }

    protected function sidFromLdap($sid)
    {
        $sidUnpacked = unpack('H*hex', $sid);
        $sidHex = array_shift($sidUnpacked);
        $subAuths = unpack('H2/H2/n/N/V*', $sid);
        if (PHP_INT_SIZE <= 4) {
            for ($i = 1; $i <= count($subAuths); $i++) {
                if ($subAuths[$i] < 0) {
                    $subAuths[$i] = $subAuths[$i] + 0x100000000;
                }
            }
        }
        $revLevel = hexdec(substr($sidHex, 0, 2));
        $authIdent = hexdec(substr($sidHex, 4, 12));

        return 'S-' . $revLevel . '-' . $authIdent . '-' . implode('-', $subAuths);
    }

    protected function getCn($dn)
    {
        $dn = str_replace('\\,', '~C0mmA~', $dn);
        preg_match('/[^,]*/', $dn, $matches, PREG_OFFSET_CAPTURE, 3);

        return str_replace('~C0mmA~', ',', $matches[0][0]);
    }

    protected function getDn($samaccountname)
    {
        $link_identifier = $this->getConnection();
        $attributes = ['dn'];
        $result = ldap_search(
            $link_identifier,
            Config::get('auth_ad_base_dn'),
            $this->groupFilter($samaccountname),
            $attributes
        );
        $entries = ldap_get_entries($link_identifier, $result);
        if ($entries['count'] > 0) {
            return $entries[0]['dn'];
        } else {
            return '';
        }
    }

    protected function userFilter($username)
    {
        // don't return disabled users
        $user_filter = "(&(samaccountname=$username)(!(useraccountcontrol:1.2.840.113556.1.4.803:=2))";

        $extra = Config::get('auth_ad_user_filter');
        if ($extra) {
            $user_filter .= $extra;
        }
        $user_filter .= ')';

        return $user_filter;
    }

    protected function groupFilter($groupname)
    {
        $group_filter = "(samaccountname=$groupname)";

        $extra = Config::get('auth_ad_group_filter');
        if ($extra) {
            $group_filter = "(&$extra$group_filter)";
        }

        return $group_filter;
    }

    protected function getFullname($username)
    {
        $connection = $this->getConnection();
        $attributes = ['name'];
        $result = ldap_search(
            $connection,
            Config::get('auth_ad_base_dn'),
            $this->userFilter($username),
            $attributes
        );
        $entries = ldap_get_entries($connection, $result);
        if ($entries['count'] > 0) {
            $membername = $entries[0]['name'][0];
        } else {
            $membername = $username;
        }

        return $membername;
    }

    public function getGroupList()
    {
        $ldap_groups = [];

        // show all Active Directory Users by default
        $default_group = 'Users';

        if (Config::has('auth_ad_group')) {
            if (Config::get('auth_ad_group') !== $default_group) {
                $ldap_groups[] = Config::get('auth_ad_group');
            }
        }

        if (! Config::has('auth_ad_groups') && ! Config::has('auth_ad_group')) {
            $ldap_groups[] = $this->getDn($default_group);
        }

        foreach (Config::get('auth_ad_groups') as $key => $value) {
            $ldap_groups[] = $this->getDn($key);
        }

        return $ldap_groups;
    }

    public function getUserlist()
    {
        $connection = $this->getConnection();

        $userlist = [];
        $ldap_groups = $this->getGroupList();

        foreach ($ldap_groups as $ldap_group) {
            $search_filter = "(&(memberOf:1.2.840.113556.1.4.1941:=$ldap_group)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
            if (Config::get('auth_ad_user_filter')) {
                $search_filter = '(&' . Config::get('auth_ad_user_filter') . $search_filter . ')';
            }
            $attributes = ['samaccountname', 'displayname', 'objectsid', 'mail'];
            $search = ldap_search($connection, Config::get('auth_ad_base_dn'), $search_filter, $attributes);
            $results = ldap_get_entries($connection, $search);

            foreach ($results as $result) {
                if (isset($result['samaccountname'][0])) {
                    $userlist[$result['samaccountname'][0]] = $this->userFromAd($result);
                }
            }
        }

        return array_values($userlist);
    }

    /**
     * Generate a user array from an AD LDAP entry
     * Must have the attributes: objectsid, samaccountname, displayname, mail
     * @internal
     *
     * @param array $entry
     * @return array
     */
    protected function userFromAd($entry)
    {
        return [
            'user_id' => $this->getUseridFromSid($this->sidFromLdap($entry['objectsid'][0])),
            'username' => $entry['samaccountname'][0],
            'realname' => $entry['displayname'][0],
            'email' => isset($entry['mail'][0]) ? $entry['mail'][0] : null,
            'descr' => '',
            'level' => $this->getUserlevel($entry['samaccountname'][0]),
            'can_modify_passwd' => 0,
        ];
    }

    public function getUser($user_id)
    {
        $connection = $this->getConnection();
        $domain_sid = $this->getDomainSid();

        $search_filter = "(&(objectcategory=person)(objectclass=user)(objectsid=$domain_sid-$user_id))";
        $attributes = ['samaccountname', 'displayname', 'objectsid', 'mail'];
        $search = ldap_search($connection, Config::get('auth_ad_base_dn'), $search_filter, $attributes);
        $entry = ldap_get_entries($connection, $search);

        if (isset($entry[0]['samaccountname'][0])) {
            return $this->userFromAd($entry[0]);
        }

        return [];
    }

    protected function getDomainSid()
    {
        $connection = $this->getConnection();

        // Extract only the domain components
        $dn_candidate = preg_replace('/^.*?DC=/i', 'DC=', Config::get('auth_ad_base_dn'));

        $search = ldap_read(
            $connection,
            $dn_candidate,
            '(objectClass=*)',
            ['objectsid']
        );
        $entry = ldap_get_entries($connection, $search);

        return substr($this->sidFromLdap($entry[0]['objectsid'][0]), 0, 41);
    }

    /**
     * Provide a connected and bound ldap connection resource
     *
     * @return resource
     */
    abstract protected function getConnection();
}
