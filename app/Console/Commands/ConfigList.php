<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use LibreNMS\Util\DynamicConfig;
use Symfony\Component\Console\Input\InputArgument;

class ConfigList extends LnmsCommand
{
    protected $name = 'config:list';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('search', InputArgument::OPTIONAL);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->configureOutputOptions();
        $search = $this->argument('search') ?? '';

        $settings = (new DynamicConfig)->all()
            ->map(fn ($v, $k): string => $this->describe($k))
            ->when($search, fn (Collection $c): Collection => $this->filter($c, $search))
            ->sortKeys()
            ->map(fn (string $descr, string $setting): array => [
                '<fg=blue>' . $this->highlight($setting, $search) . '</>',
                $this->highlight($descr, $search),
            ]
            );

        if ($settings->isEmpty()) {
            $this->error("No settings found matching '$search'");

            return 1;
        }

        $this->table(['Setting', 'Description'], $settings, 'compact');

        return 0;
    }

    private function filter(Collection $collection, string $search): Collection
    {
        return $collection->filter(function (string $value, string $key) use ($search): bool {
            return stripos($key, $search) !== false || stripos($value, $search) !== false;
        });
    }

    private function describe(string $setting): string
    {
        if (Lang::has("settings.settings.$setting.help")) {
            return __("settings.settings.$setting.help");
        }

        if (Lang::has("settings.settings.$setting.description")) {
            return __("settings.settings.$setting.description");
        }

        return '';
    }

    private function highlight(string $text, string $search): string
    {
        if ($search === '') {
            return $text;
        }

        $pattern = '/' . preg_quote($search, '/') . '/i';
        $result = preg_replace($pattern, '<options=bold>$0</>', $text);

        return is_string($result) ? $result : $text;
    }
}
