<?php

namespace LibreNMS\Alert;

use App\Models\AlertTransport;
use App\View\SimpleTemplate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\AlertState;
use LibreNMS\Interfaces\Alert\Transport as TransportInterface;

abstract class Transport implements TransportInterface
{
    protected $config;
    /**
     * @var string
     */
    protected $name;

    public static function make(string $type): TransportInterface
    {
        $class = self::getClass($type);

        return new $class();
    }

    /**
     * Returns a list of all available transports
     *
     * @return array
     */
    public static function list(): array
    {
        $list = [];
        foreach (glob(base_path('LibreNMS/Alert/Transport/*.php')) as $file) {
            $transport = strtolower(basename($file, '.php'));
            $class = self::getClass($transport);
            $instance = new $class;
            $list[$transport] = $instance->name();
        }

        return $list;
    }

    /**
     * Transport constructor.
     *
     * @param  null  $transport
     */
    public function __construct($transport = null)
    {
        if (! empty($transport)) {
            if ($transport instanceof AlertTransport) {
                $this->config = $transport->transport_config;
            } else {
                try {
                    $model = \App\Models\AlertTransport::findOrFail($transport); /** @var AlertTransport $model */
                    $this->config = $model->transport_config;
                } catch (ModelNotFoundException $e) {
                    $this->config = [];
                }
            }
        }
    }

    /**
     * @return string The display name of this transport
     */
    public function name(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        $path = explode('\\', get_called_class());

        return array_pop($path);
    }

    /**
     * Helper function to parse free form text box defined in ini style to key value pairs
     *
     * @param  string  $input
     * @param  array  $replacements  for SimpleTemplate if desired
     * @return array
     */
    protected function parseUserOptions(string $input, array $replacements = []): array
    {
        $options = [];
        foreach (preg_split('/\\r\\n|\\r|\\n/', $input, -1, PREG_SPLIT_NO_EMPTY) as $option) {
            if (Str::contains($option, '=')) {
                [$k, $v] = explode('=', $option, 2);
                $options[$k] = empty($replacements) ? trim($v) : SimpleTemplate::parse(trim($v), $replacements);
            }
        }

        return $options;
    }

    /**
     * Get the hex color string for a particular state
     *
     * @param  int  $state  State code from alert
     * @return string Hex color, default to #337AB7 blue if state unrecognised
     */
    public static function getColorForState($state)
    {
        $colors = [
            AlertState::CLEAR        => Config::get('alert_colour.ok'),
            AlertState::ACTIVE       => Config::get('alert_colour.bad'),
            AlertState::ACKNOWLEDGED => Config::get('alert_colour.acknowledged'),
            AlertState::WORSE        => Config::get('alert_colour.worse'),
            AlertState::BETTER       => Config::get('alert_colour.better'),
        ];

        return isset($colors[$state]) ? $colors[$state] : '#337AB7';
    }

    /**
     * Display the configuration details of this alert transport
     *
     * @return string
     */
    public function displayDetails(): string
    {
        $output = '';

        // Iterate through transport config template to display config details
        $config = static::configTemplate();
        foreach ($config['config'] as $item) {
            if ($item['type'] == 'oauth') {
                continue;
            }

            $val = $this->config[$item['name']];
            if ($item['type'] == 'password') {
                $val = '<b>&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</b>';
            } elseif ($item['type'] == 'select') {
                // Match value to key name for select inputs
                $val = array_search($val, $item['options']);
            }

            $output .= $item['title'] . ': ' . $val . PHP_EOL;
        }

        return $output;
    }

    /**
     * Get the alert transport class from transport type.
     *
     * @param  string  $type
     * @return string
     */
    public static function getClass(string $type): string
    {
        return 'LibreNMS\\Alert\\Transport\\' . ucfirst($type);
    }

    protected function isHtmlContent($content): bool
    {
        return $content !== strip_tags($content);
    }
}
