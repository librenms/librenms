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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use function Laravel\Prompts\form;

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
            __('commands.user:add.options.role', ['roles' => '[user, global-read, admin]']), default: ['user']);
        $this->addOption('email', 'e', InputOption::VALUE_REQUIRED);
        $this->addOption('full-name', 'l', InputOption::VALUE_REQUIRED);
        $this->addOption('descr', 's', InputOption::VALUE_REQUIRED);
    }

    public function handle(): int
    {
        if (LibrenmsConfig::get('auth_mechanism') !== 'mysql') {
            $this->warn(__('commands.user:add.wrong-auth'));
        }

        $availableRoles = Role::pluck('name')->whenEmpty(fn () => collect(['admin', 'global-read', 'user']))->all();

        $username = $this->argument('username');
        $password = $this->option('password');
        $roles = $this->option('role');
        $roles = empty($roles) ? ['user'] : $roles;
        $email = $this->option('email');
        $fullName = $this->option('full-name');
        $descr = $this->option('descr');

        if ($username && $password) {
            // cli input method
            try {
                Validator::make(['username' => $username], ['username' => $this->usernameRules()])->validate();
                Validator::make(['password' => $password], ['password' => $this->passwordRules()])->validate();
                Validator::make(['roles' => $roles],
                    ['roles' => ['required', 'array', Rule::in($availableRoles)]])->validate();
                Validator::make(['email' => $email], ['email' => ['nullable', 'email']])->validate();
            } catch (ValidationException $e) {
                $this->error($e->getMessage());

                return 1;
            }

            $this->makeUser(
                $username,
                $password,
                $roles,
                $email,
                $fullName,
                $descr,
            );

            return 0;
        }

        // interactive input
        $data = form()
            ->text(
                label: __('commands.user:add.form.username'),
                default: $username ?? '',
                required: true,
                validate: fn ($value) => $this->validatePromptInput($value, 'username', $this->usernameRules())
            )
            ->password(
                label: __('commands.user:add.form.password'),
                required: true,
                validate: fn ($value) => $this->validatePromptInput($value, 'password', $this->passwordRules())
            )
            ->multiselect(
                label: __('commands.user:add.form.roles'),
                options: $availableRoles,
                default: $roles,
                required: true,
            )
            ->text(
                label: __('commands.user:add.form.email'),
                default: $email ?? '',
                validate: fn ($value) => $this->validatePromptInput($value, 'email', ['nullable', 'email'])
            )
            ->text(__('commands.user:add.form.full-name'), default: $fullName ?? '')
            ->text(__('commands.user:add.form.descr'), default: $descr ?? '')
            ->submit();

        $this->makeUser(...$data);

        return 0;
    }

    private function usernameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            Rule::unique('users', 'username')->where('auth_type', 'mysql'),
        ];
    }

    private function passwordRules(): array
    {
        return [
            'required',
            Password::defaults(),
        ];
    }

    private function makeUser(
        string $username,
        string $password,
        array $roles,
        ?string $email,
        ?string $fullName,
        ?string $descr,
    ): void {
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
    }
}
