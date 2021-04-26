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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\User;
use Illuminate\Validation\Rule;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AddUserCommand extends LnmsCommand
{
    protected $name = 'user:add';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.user:add.description'));

        $this->addArgument('username', InputArgument::REQUIRED);
        $this->addOption('password', 'p', InputOption::VALUE_REQUIRED);
        $this->addOption('role', 'r', InputOption::VALUE_REQUIRED, __('commands.user:add.options.role', ['roles' => '[normal, global-read, admin]']), 'normal');
        $this->addOption('email', 'e', InputOption::VALUE_REQUIRED);
        $this->addOption('full-name', 'l', InputOption::VALUE_REQUIRED);
        $this->addOption('descr', 's', InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Config::get('auth_mechanism') != 'mysql') {
            $this->warn(__('commands.user:add.wrong-auth'));
        }

        $roles = [
            'normal' => 1,
            'global-read' => 5,
            'admin' => 10,
        ];

        $this->validate([
            'username' => ['required', Rule::unique('users', 'username')->where('auth_type', 'mysql')],
            'email' => 'nullable|email',
            'role' => Rule::in(array_keys($roles)),
        ]);

        // set get password
        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret(__('commands.user:add.password-request'));
        }

        $user = new User([
            'username' => $this->argument('username'),
            'level' => $roles[$this->option('role')],
            'descr' => $this->option('descr'),
            'email' => $this->option('email'),
            'realname' => $this->option('full-name'),
            'auth_type' => 'mysql',
        ]);

        $user->setPassword($password);
        $user->save();

        $user->auth_id = LegacyAuth::get()->getUserid($user->username) ?: $user->user_id;
        $user->save();

        $this->info(__('commands.user:add.success', ['username' => $user->username]));

        return 0;
    }
}
