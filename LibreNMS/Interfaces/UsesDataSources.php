<?php

namespace LibreNMS\Interfaces;

use LibreNMS\Enum\ProcessType;

/**
 * A module that gets its data from data sources, see OS::dataSources().
 * Polling methods with a data source for the module enable it by default.
 */
interface UsesDataSources
{
    /**
     * The interface of the data sources this module uses to discover or poll.
     *
     * @return class-string
     */
    public function dataSourceInterface(ProcessType $type): string;
}
