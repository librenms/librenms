<?php

return [
    'maintenance' => [
        'maintenance' => 'Wartung',
        'behavior' => [
            'options' => [
                'skip_alerts' => 'Alarme überspringen',
                'mute_alerts' => 'Alarme stummschalten',
                'run_alerts' => 'Alarme auslösen',
            ],
            'tooltip' => "- Alarme überspringen: Es werden nun neue Alarme erstellt, bestehende Alarme werden jedoch nicht abgearbeitet.
            - Alarme stummschalten: Alarme werden wie gewohnt erstellt und abgearbeitet, Benachrichtigungen an die Benutzer (z. B. per E-Mail) werden jedoch unterdrückt.
            - Alarme auslösen: Alarme werden wie gewohnt ausgeführt, die Benutzer werden benachrichtigt. Diese Option führt im Wesentlichen zu einer rein „kosmetischen“ Wartung.",
        ],
    ],
];
