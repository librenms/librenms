<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\User;
use Symfony\Component\Console\Input\InputArgument;

class ApiTokenList extends LnmsCommand
{
    protected $name = 'api:token:list';

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('username', InputArgument::REQUIRED);
    }

    public function handle(): int
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error(trans('commands.api:token:list.user-not-found', ['username' => $username]));

            return 1;
        }

        $tokens = $user->tokens;

        if ($tokens->isEmpty()) {
            $this->info(trans('commands.api:token:list.no-tokens', ['username' => $user->username]));

            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Last Used', 'Created'],
            $tokens->map(fn ($token) => [
                $token->id,
                $token->name,
                $token->last_used_at?->diffForHumans() ?? 'Never',
                $token->created_at->diffForHumans(),
            ])
        );

        return 0;
    }
}
