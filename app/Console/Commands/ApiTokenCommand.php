<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ApiTokenCommand extends LnmsCommand
{
    protected $name = 'api:token';
    protected $description = 'Manage API tokens for users';

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('action', InputArgument::REQUIRED, 'Action to perform: create, list, revoke');
        $this->addArgument('username', InputArgument::REQUIRED, 'Username to manage tokens for');
        $this->addOption('token-name', null, InputOption::VALUE_REQUIRED, 'Name for the token', 'api-token');
        $this->addOption('token-id', null, InputOption::VALUE_REQUIRED, 'Token ID to revoke (for revoke action)');
    }

    public function handle(): int
    {
        $action = $this->argument('action');
        $username = $this->argument('username');

        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error("User '{$username}' not found.");

            return 1;
        }

        return match ($action) {
            'create' => $this->createToken($user),
            'list' => $this->listTokens($user),
            'revoke' => $this->revokeToken($user),
            default => $this->invalidAction($action),
        };
    }

    private function createToken(User $user): int
    {
        $name = $this->option('token-name');
        $token = $user->createToken($name);

        $this->info('Token created successfully.');
        $this->newLine();
        $this->line($token->plainTextToken);
        $this->newLine();
        $this->warn('Save this token — it will not be shown again.');

        return 0;
    }

    private function listTokens(User $user): int
    {
        $tokens = $user->tokens;

        if ($tokens->isEmpty()) {
            $this->info("No tokens found for user '{$user->username}'.");

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

    private function revokeToken(User $user): int
    {
        $tokenId = $this->option('token-id');

        if (! $tokenId) {
            $this->error('The --token-id option is required for the revoke action.');

            return 1;
        }

        $token = $user->tokens()->where('id', $tokenId)->first();

        if (! $token) {
            $this->error("Token ID {$tokenId} not found for user '{$user->username}'.");

            return 1;
        }

        $token->delete();
        $this->info("Token '{$token->name}' (ID: {$tokenId}) revoked.");

        return 0;
    }

    private function invalidAction(string $action): int
    {
        $this->error("Unknown action '{$action}'. Use: create, list, revoke");

        return 1;
    }
}
