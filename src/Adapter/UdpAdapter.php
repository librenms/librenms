<?php
namespace InfluxDB\Adapter;

use DateTime;

final class UdpAdapter extends AdapterAbstract
{
    public function send(array $message)
    {
        $message = array_replace_recursive($this->getMessageDefaults(), $message);
        $message = $this->serialize($message);

        $this->write($message);
    }

    public function write($message)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $message, strlen($message), 0, $this->getOptions()->getHost(), $this->getOptions()->getPort());
        socket_close($socket);
    }

    private function serialize(array $message)
    {
        $tags = $this->getOptions()->getTags();

        if (array_key_exists("tags", $message)) {
            $tags = array_replace_recursive($tags, $message["tags"]);
        }

        $unixepoch = (int)(microtime(true) * 1e9);
        if (array_key_exists("time", $message)) {
            $dt = new DateTime($message["time"]);
            $unixepoch = (int)($dt->format("U") * 1e9);
        }

        $lines = [];
        foreach ($message["points"] as $point) {
            if (array_key_exists("tags", $point)) {
                $tags = array_replace_recursive($tags, $point["tags"]);
            }

            if (!$tags) {
                $lines[] = trim(
                    sprintf(
                        "%s %s %d",
                        $point["measurement"], $this->toKeyValue($point["fields"], true), $unixepoch
                    )
                );
            } else {
                $lines[] = trim(
                    sprintf(
                        "%s,%s %s %d",
                        $point["measurement"], $this->toKeyValue($tags), $this->toKeyValue($point["fields"], true), $unixepoch
                    )
                );
            }
        }
        return implode("\n", $lines);
    }

    private function toKeyValue(array $elems, $escape=false)
    {
        $list = [];
        foreach ($elems as $key => $value) {
            if ($escape && is_string($value)) {
                $value = "\"{$value}\"";
            }
            $list[] = sprintf("%s=%s", $key, $value);
        }

        return implode(",", $list);
    }
}
