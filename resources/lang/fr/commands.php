<?php

return [
    'user:add' => [
        'description' => 'Création d\'un utilisateur local. Vous pourrez utiliser les identifiants créés si vous utilisez l\'authentification mysql',
        'arguments' => [
            'username' => 'Le nom d\'utilisateur avec lequel l\'utilisateur se connectera',
        ],
        'options' => [
            'descr' => "Description de l'utilisateur",
            'email' => "Email à utiliser pour l'utilisateur",
            'password' => 'Mot de passe de l\'utilisateur, s\'il n\'est pas donné, il vous sera demandé de saisir un mot de passe.',
            'full-name' => "Nom complet de l'utilisateur",
            'role' => "Définir le rôle de l'utilisateur :roles",
        ],
        'password-request' => "Veuillez entrer le mot de passe de l'utilisateur",
        'success' => 'Utilisateur ajouté avec succès : :username',
        'wrong-auth' => "Attention !  Vous ne pourrez pas vous connecter avec cet utilisateur car vous n'utilisez pas les auth MySQL.",
    ],
];
