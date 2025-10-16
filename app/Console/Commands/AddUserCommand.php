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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use RuntimeException;
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
        $this->addOption('role', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            __('commands.user:add.options.role', ['roles' => '[user, global-read, admin]']));
        $this->addOption('email', 'e', InputOption::VALUE_REQUIRED);
        $this->addOption('full-name', 'l', InputOption::VALUE_REQUIRED);
        $this->addOption('descr', 's', InputOption::VALUE_REQUIRED);
    }

    public function handle(): int
    {
        try {
            if (LibrenmsConfig::get('auth_mechanism') !== 'mysql') {
                $this->warn(__('commands.user:add.wrong-auth'));
            }

            $shouldPromptOptional = ! $this->option('password');

            $username = $this->promptForInput('username', [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->where('auth_type', 'mysql'),
            ], fn ($validation) => text(
                label: 'Username',
                required: 'You must provide a username.',
                validate: $validation,
            ));

            $password = $this->promptForInput('password', [
                'required',
                Password::defaults(),
            ], fn ($validation) => password(
                label: __('commands.user:add.password-request'),
                required: 'You must provide a password.',
                validate: $validation,
            ));

            $availableRoles = Role::pluck('name')->whenEmpty(fn () => collect(['admin', 'global-read', 'user']))->all();
            $roles = $this->promptForInput('role', [
                'array',
                Rule::in($availableRoles),
            ], fn($validation) => multiselect(
                label: 'Select user role(s)',
                options: $availableRoles,
                default: ['user'],
                required: 'You must select at least one role.',
                validate: $validation,
            ), $shouldPromptOptional) ?? ['user'];

            $email = $this->promptForInput('email', 'nullable|email', fn ($validation) => text(
                label: 'Email (optional)',
                validate: $validation,
            ), $shouldPromptOptional);

            $fullName = $this->promptForInput('full-name', 'nullable', fn ($validation) => text(
                label: 'Full name (optional)',
                validate: $validation,
            ), $shouldPromptOptional);

            $descr = $this->promptForInput('descr', 'nullable', fn ($validation) => text(
                label: 'Description (optional)',
                validate: $validation,
            ), $shouldPromptOptional);

            $user = new User([
                'username' => $username,
                'realname' => $fullName,
                'email' => $email,
                'descr' => $descr,
                'auth_type' => 'mysql',
            ]);

            $user->setPassword($password);
            $user->save(); // assign roles requires a user_id
            $user->assignRole($roles);
            $user->save();

            $this->info(__('commands.user:add.success', ['username' => $user->username]));

            return 0;
        } catch (RuntimeException|ValidationException $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
