<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Artisan;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LibreNMS\Util\EnvHelper;
use Symfony\Component\Console\Input\InputArgument;

class KeyRotate extends LnmsCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:rotate';

    /**
     * @var Encrypter
     */
    private $decrypt;
    /**
     * @var Encrypter
     */
    private $encrypt;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('old_key', InputArgument::OPTIONAL);
        $this->addOption('generate-new-key');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $new = config('app.key');
        $cipher = config('app.cipher');

        $this->validate([
            'generate-new-key' => [
                'exclude_unless:old_key,null',
                'boolean',
            ],
            'old_key' => [
                'exclude_if:generate-new-key,true',
                'required',
                'starts_with:base64:',
                Rule::notIn([$new]),
            ],
        ]);

        // check for cached config
        if (is_file(base_path('bootstrap/cache/config.php'))) {
            Artisan::call('config:clear'); // clear config cache
            $this->warn(trans('commands.key:rotate.cleared-cache'));

            return 0;
        }

        $old = $this->argument('old_key');
        if ($this->option('generate-new-key')) {
            $old = $new; // use key in env as existing key
            $new = 'base64:' . base64_encode(
                    Encrypter::generateKey($this->laravel['config']['app.cipher'])
                );
        }

        $this->line(trans('commands.key:rotate.old_key', ['key' => $old]));
        $this->line(trans('commands.key:rotate.new_key', ['key' => $new]));
        $this->error(trans('commands.key:rotate.backup_keys'));
        $this->newLine();

        // init encrypters
        $this->decrypt = $this->createEncrypter($old, $cipher);
        $this->encrypt = $this->createEncrypter($new, $cipher);

        $this->line(trans('commands.key:rotate.backups'));
        if (! $this->confirm(trans('commands.key:rotate.confirm'))) {
            return 1;
        }

        $success = $this->rekeyConfigData('validation.encryption.test');

        if (! $success) {
            $this->line(trans('commands.key:rotate.old_key', ['key' => $old]));
            $this->line(trans('commands.key:rotate.new_key', ['key' => $new]));
            $this->error(trans('commands.key:rotate.failed'));

            return 1;
        }

        $this->info(trans('commands.key:rotate.success'));

        if ($this->option('generate-new-key') && $this->confirm(trans('commands.key:rotate.save_key'))) {
            EnvHelper::writeEnv([
                'OLD_APP_KEY' => $old,
                'APP_KEY' => $new,
            ], ['OLD_APP_KEY', 'APP_KEY']);
        }

        return 0;
    }

    private function createEncrypter(string $key, string $cipher): Encrypter
    {
        return new Encrypter(base64_decode(Str::after($key, 'base64:')), $cipher);
    }

    private function rekeyConfigData(string $key): bool
    {
        if (! \LibreNMS\Config::has($key)) {
            return true;
        }

        try {
            $data = $this->decrypt->decryptString(\LibreNMS\Config::get($key));
            \LibreNMS\Config::persist($key, $this->encrypt->encryptString($data));

            return true;
        } catch (DecryptException $e) {
            try {
                $this->encrypt->decryptString(\LibreNMS\Config::get($key));

                return true; // already rotated
            } catch (DecryptException $e) {
                $this->warn(trans('commands.key:rotate.decrypt-failed', ['item' => $key]));

                return false;
            }
        }
    }
}
