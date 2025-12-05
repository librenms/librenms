<?php

// Parser should return an array with type, descr, circuit, speed and notes

return function (string $ifAlias): array {
    $split = preg_split('/[:\[\]{}()]/', $ifAlias);
    $type = trim($split[0] ?? '');
    $descr = trim($split[1] ?? '');

    if ($type && $descr) {
        return [
            'type' => strtolower($type),
            'descr' => $descr,
            'circuit' => trim(preg_split('/[{}]/', $ifAlias)[1] ?? ''),
            'speed' => trim(preg_split('/[\[\]]/', $ifAlias)[1] ?? ''),
            'notes' => trim(preg_split('/[()]/', $ifAlias)[1] ?? ''),
        ];
    }

    return [];
};
