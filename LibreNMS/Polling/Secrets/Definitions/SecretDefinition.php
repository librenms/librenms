<?php

namespace LibreNMS\Polling\Secrets\Definitions;

use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Secrets\Data\SecretData;

interface SecretDefinition extends HasFieldSchema
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createData(array $data): SecretData;
}
