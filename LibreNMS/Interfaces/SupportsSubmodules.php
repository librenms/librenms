<?php

namespace LibreNMS\Interfaces;

interface SupportsSubmodules
{
    /**
     * Restrict the next discover/poll run to the given submodules.
     * Null means no override, run the module's default set.
     *
     * @param  string[]|null  $submodules
     */
    public function setSubmodules(?array $submodules): void;
}
