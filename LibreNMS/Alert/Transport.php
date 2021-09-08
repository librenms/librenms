<?php

namespace LibreNMS\Alert;

use App\Models\AlertTransport;
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
                    $this->config = \App\Models\AlertTransport::findOrFail($transport)->transport_config;
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
     * @return array
     */
    protected function parseUserOptions($input)
    {
        $options = [];
        foreach (explode(PHP_EOL, $input) as $option) {
            if (Str::contains($option, '=')) {
                [$k,$v] = explode('=', $option, 2);
                $options[$k] = trim($v);
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
     * Get the alert transport class from transport type.
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
