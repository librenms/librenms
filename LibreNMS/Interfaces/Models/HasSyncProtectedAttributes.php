<?php

namespace LibreNMS\Interfaces\Models;

interface HasSyncProtectedAttributes
{
    /**
     * Attributes of this existing model that syncing discovered models must not overwrite.
     * Used to keep user set values, they are still set when the model is created.
     *
     * @return string[]
     */
    public function getSyncProtectedAttributes(): array;
}
