<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputDefinition;
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
            $commands = $this->getApplication()->all();
            if (isset($commands[$command])) {
                $command_def = $commands[$command]->getDefinition();
                $previous_name = ltrim($previous, '-');

                if (starts_with($previous, '-') && $command_def->hasOption($previous_name) && $command_def->getOption($previous_name)->acceptValue()) {
                    $completions = $this->completeOptionValue($command_def->getOption($previous_name), $current);
                } else {
                    $completions = collect();
                    if (!starts_with($previous, '-')) {
                        $completions = $this->completeArguments($command, $current, end($words));
                    }
                    $completions = $completions->merge($this->completeOption($command_def, $current));
                }
            }
        }

        \Log::debug('Bash completion values', get_defined_vars());

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
     * Complete a command
     *
     * @param string $partial
     * @return \Illuminate\Support\Collection
     */
    private function completeCommand($partial)
    {
        $all_commands = collect(\Artisan::all())->keys()->filter(function ($cmd) {
            return $cmd != 'list:bash-completion';
        });

        $completions = $all_commands->filter(function ($cmd) use ($partial) {
            return empty($partial) || starts_with($cmd, $partial);
        });

        // handle : silliness
        if (str_contains($partial, ':')) {
            $completions = $completions->map(function ($cmd) {
                return substr($cmd, strpos($cmd, ':') + 1);
            });
        }
        return $completions;
    }

    /**
     * Complete options for the given command
     *
     * @param InputDefinition $command
     * @param string $partial
     * @return \Illuminate\Support\Collection
     */
    private function completeOption($command, $partial)
    {
        // default options
        $options = collect([
            '-h',
            '--help',
            '-V',
            '--version',
            '--ansi',
            '--no-ansi',
            '-n',
            '--no-interaction',
            '--env',
            '-v',
            '-vv',
            '-vvv',
        ]);

        if ($command) {
            $options = collect($command->getOptions())
                ->flatMap(function ($option) {
                    return $this->parseOption($option);
                })->merge($options);
        }

        return $options->filter(function ($option) use ($partial) {
            return empty($partial) || starts_with($option, $partial);
        });
    }

    /**
     * Complete options with values (if a list is enumerate in the description)
     *
     * @param InputOption $option
     * @param string $partial
     * @return \Illuminate\Support\Collection
     */
    private function completeOptionValue($option, $partial)
    {
        if ($option && preg_match('/\[(.+)\]/', $option->getDescription(), $values)) {
            return collect(explode(',', $values[1]))
                ->map(function ($value) {
                    return trim($value);
                })
                ->filter(function ($value) use ($partial) {
                    return empty($partial) || starts_with($value, $partial);
                });
        }
        return collect();
    }

    /**
     * Complete commands with arguments
     *
     * @param string $command Name of the current command
     * @param string $partial
     * @param string $current_word
     * @return \Illuminate\Support\Collection
     */
    private function completeArguments($command, $partial, $current_word)
    {
        switch ($command) {
            case 'device:remove':
                // fall through
            case 'device:rename':
                $device_query = Device::select('hostname')->limit(5)->orderBy('hostname');
                if ($partial) {
                    $device_query->where('hostname', 'like', $partial . '%');
                }

                return $device_query->pluck('hostname');
                break;
            case 'help':
                return $this->completeCommand($current_word);
                break;
            default:
                return collect();
        }
    }
}
