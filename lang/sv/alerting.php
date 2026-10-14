<?php

return [
    'maintenance' => [
        'maintenance' => 'Underhåll',
        'behavior' => [
            'options' => [
                'skip_alerts' => 'Hoppa över varningar',
                'mute_alerts' => 'Stäng av aviseringar',
                'run_alerts' => 'Kör varningar',
            ],
            'tooltip' => '- Hoppa över varningar: Nu kommer nya varningar att skapas och befintliga varningar kommer inte att lösas.
        - Stäng av varningar: Varningar skapas och löses som vanligt, men alla typer av användarmeddelanden (som e-post) är undertryckta
        - Kör varningar: Varningar körs som vanligt, användare meddelas. Det här alternativet leder i huvudsak till ett "enbart kosmetiskt" underhåll',
        ],
        'title' => 'Titel',
    ],
];
