<?php

namespace LibreNMS\Alert;

use Illuminate\Support\Collection;

/**
 * @extends Collection<string, mixed>
 */
class TemplateAlertData extends Collection
{
    public function __get($name): mixed
    {
        return $this->get($name, "$name is not a valid \$alert data name");
    }

    public function __isset(string $name): bool
    {
        return isset($this->items[$name]);
    }
}
