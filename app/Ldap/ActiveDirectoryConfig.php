<?php

namespace App\Ldap;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\AuthenticationException;

class ActiveDirectoryConfig
{
    /**
     * @return array{hosts: list<string>, port: int, base_dn: string, username: string, password: string, timeout: int, use_tls: bool, use_starttls: bool, options: array<int, int>}
     */
    public function connection(): array
    {
        $domain = LibrenmsConfig::get('auth_ad_domain', '');
        $urls = preg_split('/\s+/', trim(LibrenmsConfig::get('auth_ad_url') ?: 'ldap://' . ($domain ?: 'localhost')));
        $hosts = [];
        $scheme = null;
        $port = null;
        foreach ($urls as $url) {
            $parts = parse_url($url);
            $hostScheme = $parts['scheme'] ?? '';
            $hostPort = $parts['port'] ?? ($hostScheme === 'ldaps' ? 636 : 389);
            if (empty($parts['host']) || ! in_array($hostScheme, ['ldap', 'ldaps']) ||
                ($scheme !== null && ($scheme !== $hostScheme || $port !== $hostPort))) {
                throw new AuthenticationException('AD servers must use valid LDAP URLs with the same protocol and port');
            }
            $hosts[] = $parts['host'];
            $scheme = $hostScheme;
            $port = $hostPort;
        }

        $baseDn = LibrenmsConfig::get('auth_ad_base_dn') ?: ($domain ? 'dc=' . implode(',dc=', explode('.', $domain)) : '');
        $options = [LDAP_OPT_X_TLS_REQUIRE_CERT => LibrenmsConfig::get('auth_ad_check_certificates') ? LDAP_OPT_X_TLS_DEMAND : LDAP_OPT_X_TLS_NEVER];

        return [
            'hosts' => $hosts,
            'port' => $port,
            'base_dn' => $baseDn,
            'username' => $this->bindUsername(LibrenmsConfig::get('auth_ad_binddn') ?: LibrenmsConfig::get('auth_ad_binduser', '')),
            'password' => LibrenmsConfig::get('auth_ad_bindpassword', ''),
            'timeout' => (int) LibrenmsConfig::get('auth_ad_timeout', 5),
            'use_tls' => $scheme === 'ldaps',
            'use_starttls' => $scheme !== 'ldaps' && in_array(LibrenmsConfig::get('auth_ad_starttls'), ['optional', 'required']),
            'options' => $options,
        ];
    }

    public function credentials(array $credentials): array
    {
        if (LibrenmsConfig::get('auth_mechanism') !== 'active_directory') {
            return $credentials;
        }

        $username = (string) ($credentials['username'] ?? '');
        $attribute = str_contains($username, '@') ? 'userprincipalname' : 'samaccountname';
        if (str_contains($username, '\\')) {
            $username = substr($username, strrpos($username, '\\') + 1);
        }
        $ldap = [$attribute => $username, 'password' => $credentials['password'] ?? ''];
        if (LibrenmsConfig::get('auth_fallback_local')) {
            $ldap['fallback'] = [
                'username' => $credentials['username'] ?? '',
                'password' => $credentials['password'] ?? '',
                'auth_type' => 'mysql',
                'enabled' => 1,
            ];
        }

        return $ldap;
    }

    private function bindUsername(string $username): string
    {
        if ($username === '' || strpbrk($username, '@\\=') !== false) {
            return $username;
        }

        $domain = LibrenmsConfig::get('auth_ad_domain', '');

        return $domain ? $username . '@' . $domain : $username;
    }
}
