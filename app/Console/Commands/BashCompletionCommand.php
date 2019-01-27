<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class BashCompletionCommand extends Command
{
    protected $hidden = true;  // don't show this command in the list

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:bash-completion {this_command?} {current?} {previous?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a bash completion response';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $line = getenv('COMP_LINE');
        $current = getenv('COMP_CURRENT');
        $previous = getenv('COMP_PREVIOUS');
        $words = explode(' ', $line);

        $command = isset($words[1]) ? $words[1] : $current; // handle : silliness

        if (count($words) < 3) {
            $completions = $this->completeCommand($command);
        } else {
            $completions = collect();

            /** @var Command $cmd */
            $cmd = $this->getApplication()->all()[$command];
            // handle options with values (if a list is enumerate in the description)
            if (starts_with($previous, '-') && ($previous_option = $cmd->getDefinition()->getOption(ltrim($previous, '-')))->acceptValue()) {
                if (preg_match('/\[(.+)\]/', $previous_option->getDescription(), $values)) {
                    $completions = $completions->merge(collect(explode(',', $values[1]))
                        ->map(function ($value) {
                            return trim($value);
                        })
                        ->filter(function ($value) use ($current) {
                            return empty($current) || starts_with($value, $current);
                        }));
                }
            } elseif (starts_with($current, '-')) {
                $completions = collect($cmd->getDefinition()->getOptions())
                    ->flatMap(function ($option) {
                        return $this->parseOption($option);
                    })->filter(function ($option) use ($current) {
                        return empty($current) || starts_with($option, $current);
                    });
            } else {
                // handle commands with arguments
                switch ($command) {
                    case 'device:remove':
                        // fall through
                    case 'device:rename':
                        $device_query = Device::select('hostname')->limit(5)->orderBy('hostname');
                        if ($current) {
                            $device_query->where('hostname', 'like', $current . '%');
                        }

                        $completions = $completions->merge($device_query->pluck('hostname'));
                        break;
                    case 'help':
                        $completions = $completions->merge($this->completeCommand(end($words)));
                    default:
                }
            }
        }

        \Log::critical(json_encode(get_defined_vars(), JSON_PRETTY_PRINT));

        echo $completions->implode(PHP_EOL);
        return 0;
    }

    private function parseOption(InputOption $def)
    {
        $opts = [];

        if ($shortcut = $def->getShortcut()) {
            $opts[] = '-' . $shortcut;
        }

        if ($name = $def->getName()) {
            $opts[] = '--' . $name;
        }

        return $opts;
    }

    /**
     * @param static $all_commands
     * @param $command
     * @return mixed
     */
    private function completeCommand($command)
    {
        $all_commands = collect(\Artisan::all())->keys()->filter(function ($cmd) {
            return $cmd != 'list:bash-completion';
        });

        $completions = $all_commands->filter(function ($cmd) use ($command) {
            return empty($command) || starts_with($cmd, $command);
        });

        // handle : silliness
        if (str_contains($command, ':')) {
            $completions = $completions->map(function ($cmd) {
                return substr($cmd, strpos($cmd, ':') + 1);
            });
        }
        return $completions;
    }

}
