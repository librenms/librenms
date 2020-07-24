<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;

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
        $completions = collect();
        $line = getenv('COMP_LINE');
        $current = getenv('COMP_CURRENT');
        $previous = getenv('COMP_PREVIOUS');
        $words = explode(' ', $line);

        $command_name = isset($words[1]) ? $words[1] : $current; // handle : silliness

        if (count($words) < 3) {
            $completions = $this->completeCommand($command_name);
        } else {
            $commands = $this->getApplication()->all();
            if (isset($commands[$command_name])) {
                $command = $commands[$command_name];
                $command_def = $command->getDefinition();
                $input = new StringInput(implode(' ', array_slice($words, 2)));
                try {
                    $input->bind($command_def);
                } catch (\RuntimeException $e) {
                    // ignore?
                }

                // check if the command can complete arguments
                if (method_exists($command, 'completeArgument')) {
                    foreach ($input->getArguments() as $name => $value) {
                        if ($current == $value) {
                            $values = $command->completeArgument($name, $value);
                            if (! empty($values)) {
                                echo implode(PHP_EOL, $values);

                                return 0;
                            }
                            break;
                        }
                    }
                }

                if ($option = $this->optionExpectsValue($current, $previous, $command_def)) {
                    $completions = $this->completeOptionValue($option, $current);
                } else {
                    $completions = collect();
                    if (! Str::startsWith($previous, '-')) {
                        $completions = $this->completeArguments($command_name, $current, end($words));
                    }
                    $completions = $completions->merge($this->completeOption($command_def, $current, $this->getPreviousOptions($words)));
                }
            }
        }

        \Log::debug('Bash completion values', get_defined_vars());

        echo $completions->implode(PHP_EOL);

        return 0;
    }

    /**
     * @param string $current
     * @param string $previous
     * @param InputDefinition $command_def
     * @return false|InputOption
     */
    private function optionExpectsValue($current, $previous, $command_def)
    {
        // handle long option =
        if (Str::startsWith($current, '--') && Str::contains($current, '=')) {
            [$previous, $current] = explode('=', $current);
        }

        if (Str::startsWith($previous, '-')) {
            $name = ltrim($previous, '-');
            if ($command_def->hasOption($name) && $command_def->getOption($name)->acceptValue()) {
                return $command_def->getOption($name);
            }

            if ($command_def->hasShortcut($name) && $command_def->getOptionForShortcut($name)->acceptValue()) {
                return $command_def->getOptionForShortcut($name);
            }
        }

        return false;
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
            return empty($partial) || Str::startsWith($cmd, $partial);
        });

        // handle : silliness
        if (Str::contains($partial, ':')) {
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
     * @param array $prev_options Previous words in the command
     * @return \Illuminate\Support\Collection
     */
    private function completeOption($command, $partial, $prev_options)
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
                ->flatMap(function ($option) use ($prev_options) {
                    $option_flags = $this->parseOption($option);
                    // don't return previously specified options
                    if (array_intersect($option_flags, $prev_options)) {
                        return [];
                    }

                    return $option_flags;
                })->merge($options);
        }

        return $options->filter(function ($option) use ($partial) {
            return empty($partial) || Str::startsWith($option, $partial);
        });
    }

    private function getPreviousOptions($words)
    {
        return array_reduce($words, function ($result, $word) {
            if (Str::startsWith($word, '-')) {
                $split = explode('=', $word, 2); // users may use equals for values
                $result[] = reset($split);
            }

            return $result;
        }, []);
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
                    return empty($partial) || Str::startsWith($value, $partial);
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
            case 'help':
                return $this->completeCommand($current_word);
            default:
                return collect();
        }
    }
}
