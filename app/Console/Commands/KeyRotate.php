<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Artisan;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LibreNMS\Config;
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
        $this->addArgument('old_key', InputArgument::REQUIRED);
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
            'old_key' => [
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

        // init encrypters
        $this->decrypt = $this->createEncrypter($new, $cipher);
        $this->encrypt = $this->createEncrypter($this->argument('old_key'), $cipher);

        $this->rekeyConfigData('validation.encryption.test');

        return 0;
    }

    private function createEncrypter(string $key, string $cipher): Encrypter
    {
        return new Encrypter(base64_decode(Str::after($key, 'base64:')), $cipher);
    }

    private function rekeyConfigData(string $key): bool
    {
        try {
            $data = $this->decrypt->decryptString(Config::get($key));
            Config::set($key, $this->encrypt->encryptString($data));

            return true;
        } catch (DecryptException $e) {
            $this->warn(trans('commands.key:rotate.decrypt-failed', ['item' => $key]));

            return false;
        }
    }
}
