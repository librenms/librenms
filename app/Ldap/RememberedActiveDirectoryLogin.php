<?php

namespace App\Ldap;

use App\Facades\LibrenmsConfig;
use App\Ldap\Rules\ActiveDirectoryAccess;
use App\Models\User;
use LdapRecord\Laravel\LdapUserRepository;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Attributes\Guid;

class RememberedActiveDirectoryLogin
{
    public function __construct(private readonly ActiveDirectoryAccess $access) {}

    public function allows(User $user, LdapUserRepository $directory): bool
    {
        return $user->enabled && match ($user->auth_type) {
            'active_directory' => $this->directoryAllows($user, $directory),
            'mysql' => LibrenmsConfig::get('auth_fallback_local'),
            default => false,
        };
    }

    private function directoryAllows(User $user, LdapUserRepository $directory): bool
    {
        $directoryId = $user->getLdapGuid() ?? '';
        $hasLegacyId = ctype_digit($directoryId);

        try {
            $entry = match (true) {
                Guid::isValid($directoryId) => $directory->findByModel($user),
                $hasLegacyId => $directory->findBy('samaccountname', $user->username),
                default => null,
            };

            // The legacy provider stored the last component of the account's SID.
            return $entry instanceof ActiveDirectoryUser
                && (! $hasLegacyId || str_ends_with($entry->getConvertedSid() ?? '', '-' . $directoryId))
                && $this->access->passes($entry, $user);
        } catch (LdapRecordException $exception) {
            report($exception);

            return false;
        }
    }
}
