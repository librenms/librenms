<?php

return [
    'user:add' => [
        'description' => 'Füge einen lokalen Benutzer hinzu. Sie können sich nur einloggen wenn die Authentifizierung auf MySQL gesetzt ist',
        'arguments' => [
            'username' => 'Die Authentifizierung mit der sich der Benutzer einloggt',
        ],
        'options' => [
            'descr' => 'Beschreibung',
            'email' => 'E-Mail',
            'password' => 'Passwort des Benutzers. Wenn nicht angegeben werden Sie danach gefragt',
            'full-name' => 'Voller Name des Benutzers',
            'role' => 'Deklariere dem Benutzer die Rolle :roles',
        ],
        'password-request' => 'Definieren Sie ein Benutzerpasswort',
        'success' => 'Benutzer :username erfolgreich hinzugefügt',
        'wrong-auth' => 'Achtung! Sie können sich nicht mit diesem Benutzernamen einloggen wenn die Authentifizierung nicht auf MySQL gesetzt ist',
    ],
];
