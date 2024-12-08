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
use App\Models\User;
use Bouncer;
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
        $this->addOption('role', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, __('commands.user:add.options.role', ['roles' => '[user, global-read, admin]']), ['user']);
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

        $roles = Bouncer::role()->pluck('name');

        $this->validate([
            'username' => ['required', Rule::unique('users', 'username')->where('auth_type', 'mysql')],
            'email' => 'nullable|email',
            'role.*' => Rule::in($roles),
        ]);

        // set get password
        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret(__('commands.user:add.password-request'));
        }

        $user = new User([
            'username' => $this->argument('username'),
            'descr' => $this->option('descr'),
            'email' => $this->option('email'),
            'realname' => $this->option('full-name'),
            'auth_type' => 'mysql',
        ]);

        $user->setPassword($password);
        $user->save();
        $user->assign($this->option('role'));

        $user->auth_id = (string) LegacyAuth::get()->getUserid($user->username) ?: $user->user_id;
        $user->save();

        $this->info(__('commands.user:add.success', ['username' => $user->username]));

        return 0;
    }
}
