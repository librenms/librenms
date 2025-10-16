<?php

/**
 * AddUserCommand.php
 *
 * CLI command to add a user to LibreNMS
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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use LibreNMS\Authentication\LegacyAuth;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class AddUserCommand extends LnmsCommand
{
    protected $name = 'user:add';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.user:add.description'));
        $this->addArgument('username', InputArgument::OPTIONAL);
        $this->addOption('password', 'p', InputOption::VALUE_REQUIRED);
        $this->addOption('role', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, __('commands.user:add.options.role', ['roles' => '[user, global-read, admin]']));
        $this->addOption('email', 'e', InputOption::VALUE_REQUIRED);
        $this->addOption('full-name', 'l', InputOption::VALUE_REQUIRED);
        $this->addOption('descr', 's', InputOption::VALUE_REQUIRED);
    }

    public function handle(): int
    {
        if (LibrenmsConfig::get('auth_mechanism') !== 'mysql') {
            $this->warn(__('commands.user:add.wrong-auth'));
        }

        $username = $this->getUsername();
        if (!$username) {
            return 1;
        }

        $password = $this->getPassword();
        if (!$password) {
            return 1;
        }

        $roles = $this->getRoles();
        if ($roles === null) {
            return 1;
        }

        $email = $this->getEmail();
        if ($email === false) {
            return 1;
        }

        $user = $this->createUser($username, $password, $email, $roles);

        $this->info(__('commands.user:add.success', ['username' => $user->username]));

        return 0;
    }

    private function getUsername(): ?string
    {
        $username = $this->argument('username');

        if (!$username) {
            return text(
                label: 'Username',
                required: true,
                validate: fn($value) => $this->usernameExists($value)
                    ? "Username '{$value}' already exists for mysql auth."
                    : null
            );
        }

        if ($this->usernameExists($username)) {
            $this->error("Username '{$username}' already exists for mysql auth.");
            return null;
        }

        return $username;
    }

    private function getPassword(): string|false
    {
        $password = $this->option('password');
        $needsPrompt = !$this->argument('username') || !$password;

        if ($needsPrompt) {
            return password(
                label: __('commands.user:add.password-request'),
                required: true,
                validate: fn($value) => $this->validatePassword($value)
            );
        }

        $error = $this->validatePassword($password);
        if ($error) {
            $this->error($error);
            return false;
        }

        return $password;
    }

    private function getRoles(): ?array
    {
        $availableRoles = Role::pluck('name')
            ->whenEmpty(fn() => collect(['admin', 'global-read', 'user']));

        $providedRoles = $this->option('role');

        if ($this->hasExplicitRoleOption($providedRoles)) {
            return $this->validateProvidedRoles($providedRoles, $availableRoles);
        }

        if ($this->isFullyInteractive()) {
            return multiselect(
                label: 'Select user role(s)',
                options: $availableRoles->all(),
                default: ['user']
            );
        }

        return ['user'];
    }

    private function getEmail(): string|false|null
    {
        $email = $this->option('email');

        if ($email) {
            $error = $this->validateEmail($email);
            if ($error) {
                $this->error($error);
                return false;
            }
            return $email;
        }

        if ($this->isFullyInteractive()) {
            return text(
                label: 'Email (optional)',
                required: false,
                validate: fn($value) => $this->validateEmail($value)
            );
        }

        return null;
    }

    private function createUser(string $username, string $password, ?string $email, array $roles): User
    {
        $user = new User([
            'username' => $username,
            'realname' => $this->getFullName(),
            'email' => $email,
            'descr' => $this->getUserDescription(),
            'auth_type' => 'mysql',
        ]);

        $user->setPassword($password);
        $user->save();
        $user->assignRole($roles);

        $user->auth_id = (string)(LegacyAuth::get()->getUserid($user->username) ?: $user->user_id);
        $user->save();

        return $user;
    }

    private function getFullName(): ?string
    {
        $fullName = $this->option('full-name');

        if ($fullName) {
            return $fullName;
        }

        if ($this->isFullyInteractive()) {
            return text(label: 'Full name (optional)', required: false);
        }

        return null;
    }

    private function getUserDescription(): ?string
    {
        $descr = $this->option('descr');

        if ($descr) {
            return $descr;
        }

        if ($this->isFullyInteractive()) {
            return text(label: 'Description (optional)', required: false);
        }

        return null;
    }

    private function isFullyInteractive(): bool
    {
        return !$this->argument('username');
    }

    private function hasExplicitRoleOption(array $providedRoles): bool
    {
        return !empty($providedRoles) && $this->input->hasOption('role');
    }

    private function validateProvidedRoles(array $providedRoles, $availableRoles): ?array
    {
        foreach ($providedRoles as $roleName) {
            if (!in_array($roleName, $availableRoles->all(), true)) {
                $this->error("Invalid role '{$roleName}'. Allowed roles: " . $availableRoles->implode(', '));
                return null;
            }
        }

        return $providedRoles;
    }

    private function usernameExists(string $username): bool
    {
        return User::where('username', $username)
            ->where('auth_type', 'mysql')
            ->exists();
    }

    private function validatePassword(?string $password): ?string
    {
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', Password::defaults()]]
        );

        return $validator->fails()
            ? $validator->errors()->first('password')
            : null;
    }

    private function validateEmail(?string $email): ?string
    {
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'nullable|email']
        );

        return $validator->fails()
            ? $validator->errors()->first('email')
            : null;
    }
}
