<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeviceTagsDefine extends LnmsCommand
{
    protected $name = 'device:define-tags';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addArgument('tags', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Tag or list of tags to define');
        $this->addOption('json', 'j', InputOption::VALUE_NONE, 'Output results as JSON');
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, "Tag type ({implode(', ', \\App\\Models\\DeviceTagKey::\$allowedTypes)})", 'string');
        $this->addOption('hidden', null, InputOption::VALUE_NONE, 'Set tag visibility (default: visible)');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $tags = $this->argument('tags');
        $type = $this->option('type');
        $hidden = $this->option('hidden');

        $result = [];
        foreach ($tags as $tag) {
            $tagKey = \App\Models\DeviceTagKey::updateOrCreate(
                ['key' => $tag],
                ['type' => $type, 'visible' => ! $hidden]
            );
            $result[] = [
                'key' => $tagKey->key,
                'type' => $tagKey->type,
                'visible' => $tagKey->visible ? 'true' : 'false',
            ];
        }

        $this->renderOutput($result);

        return 0;
    }

    private function renderOutput($tags): void
    {
        if ($tags) {
            if ($this->option('json')) {
                $this->line(json_encode($tags));

                return;
            }

            foreach ($tags as $value) {
                $this->line("{$value['key']}.key=" . $value['key']);
                $this->line("{$value['key']}.type=" . $value['type']);
                $this->line("{$value['key']}.visible=" . ($value['visible'] ? 'true' : 'false'));
            }
        }
    }
}
