<?php

namespace LibreNMS\Polling\Method\Definitions;

final class UnknownDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-question';
    }

    public function fields(): array
    {
        return [];
    }
}
