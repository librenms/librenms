<?php

return [
    'device' => [
        'title' => 'Enheter',
        'viewAll' => [
            'label' => 'Visa alla enheter',
            'description' => 'Visa alla enheter',
        ],
        'view' => [
            'label' => 'Visa enhetsdetaljer',
            'description' => 'Visa enheter som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Lägg till enheter',
            'description' => 'Lägg till nya enheter till LibreNMS',
        ],
        'update' => [
            'label' => 'Redigera enheter',
            'description' => 'Ändra enhetsinställningar',
        ],
        'delete' => [
            'label' => 'Ta bort enheter',
            'description' => 'Ta bort enheter från LibreNMS',
        ],
        'debug' => [
            'label' => 'Felsöka enheter',
            'description' => 'Kör snmpwalk och andra felsökningskommandon på enheter',
        ],
        'showConfig' => [
            'label' => 'Visa enhetskonfiguration',
            'description' => 'Visa enhetskonfiguration',
        ],
        'updateNotes' => [
            'label' => 'Uppdatera enhetsanteckningar',
            'description' => 'Uppdatera enhetsanteckningar',
        ],
    ],
    'alert' => [
        'title' => 'Varningar',
        'viewAll' => [
            'label' => 'Visa alla varningar',
            'description' => 'Visa alla varningar',
        ],
        'view' => [
            'label' => 'Visa varningsdetaljer',
            'description' => 'Visa varningar för enheter som användaren kan komma åt',
        ],
        'detail' => [
            'label' => 'Visa varningsdetaljer',
            'description' => 'Visa detaljerad varningsinformation',
        ],
        'update' => [
            'label' => 'Redigera varningar',
            'description' => 'Bekräfta eller ändra varningar',
        ],
        'delete' => [
            'label' => 'Ta bort varningar',
            'description' => 'Radera varningshistorik',
        ],
    ],
    'alert-rule' => [
        'title' => 'Varningsregler',
        'viewAll' => [
            'label' => 'Visa alla varningsregler',
            'description' => 'Se alla varningsregler',
        ],
        'view' => [
            'label' => 'Visa varningsregel',
            'description' => 'Visa varningsregeldetaljer för enheter som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Skapa varningsregler',
            'description' => 'Skapa nya varningsregler',
        ],
        'update' => [
            'label' => 'Redigera varningsregler',
            'description' => 'Ändra befintliga varningsregler',
        ],
        'delete' => [
            'label' => 'Ta bort varningsregler',
            'description' => 'Ta bort varningsregler',
        ],
    ],
    'alert-schedule' => [
        'title' => 'Varningsscheman',
        'view' => [
            'label' => 'Visa varningsschema',
            'description' => 'Visa information om varningsschema',
        ],
        'create' => [
            'label' => 'Skapa varningsscheman',
            'description' => 'Skapa nya varningsscheman',
        ],
        'update' => [
            'label' => 'Redigera varningsscheman',
            'description' => 'Ändra befintliga varningsscheman',
        ],
        'delete' => [
            'label' => 'Ta bort varningsscheman',
            'description' => 'Ta bort varningsscheman',
        ],
    ],
    'alert-template' => [
        'title' => 'Varningsmallar',
        'view' => [
            'label' => 'Visa varningsmallar',
            'description' => 'Visa varningsmallar',
        ],
        'create' => [
            'label' => 'Skapa varningsmallar',
            'description' => 'Skapa nya varningsmallar',
        ],
        'update' => [
            'label' => 'Redigera varningsmallar',
            'description' => 'Ändra befintliga varningsmallar',
        ],
        'delete' => [
            'label' => 'Ta bort varningsmallar',
            'description' => 'Ta bort varningsmallar',
        ],
    ],
    'alert-transport' => [
        'title' => 'Varna Transporter',
        'view' => [
            'label' => 'Se Alert Transports',
            'description' => 'Se varningstransporter',
        ],
        'create' => [
            'label' => 'Skapa varningstransporter',
            'description' => 'Skapa nya varningstransporter',
        ],
        'update' => [
            'label' => 'Redigera varningstransporter',
            'description' => 'Ändra befintliga varningstransporter',
        ],
        'delete' => [
            'label' => 'Ta bort Alert Transports',
            'description' => 'Ta bort varningstransporter',
        ],
    ],
    'api' => [
        'title' => 'API-åtkomst',
        'access' => [
            'label' => 'API-åtkomst',
            'description' => 'Få åtkomst till LibreNMS REST API',
        ],
    ],
    'application' => [
        'title' => 'Ansökningar',
        'update' => [
            'label' => 'Uppdatera applikation',
            'description' => 'Uppdatera applikationsdata',
        ],
    ],
    'auth-log' => [
        'title' => 'Autentiseringsloggar',
        'view' => [
            'label' => 'Visa autentiseringsloggar',
            'description' => 'Visa autentiseringsloggar',
        ],
    ],
    'bill' => [
        'title' => 'Räkningar',
        'viewAll' => [
            'label' => 'Visa alla räkningar',
            'description' => 'Visa alla faktureringsuppgifter',
        ],
        'view' => [
            'label' => 'Visa räkningsinformation',
            'description' => 'Visa faktureringsdetaljer och diagram för räkningar som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Skapa räkningar',
            'description' => 'Skapa nya faktureringsposter',
        ],
        'update' => [
            'label' => 'Redigera räkningar',
            'description' => 'Ändra faktureringsinställningar',
        ],
        'delete' => [
            'label' => 'Ta bort räkningar',
            'description' => 'Ta bort faktureringsuppgifter',
        ],
    ],
    'component' => [
        'title' => 'Komponenter',
        'update' => [
            'label' => 'Uppdatera komponent',
            'description' => 'Uppdatera komponentdata',
        ],
    ],
    'custom-map' => [
        'title' => 'Kartor',
        'viewAll' => [
            'label' => 'Visa alla kartor',
            'description' => 'Visa alla nätverkskartor',
        ],
        'view' => [
            'label' => 'Visa karta',
            'description' => 'Visa nätverkskartor som innehåller enheter som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Skapa kartor',
            'description' => 'Skapa nya nätverkskartor',
        ],
        'update' => [
            'label' => 'Redigera kartor',
            'description' => 'Ändra befintliga nätverkskartor',
        ],
        'delete' => [
            'label' => 'Ta bort kartor',
            'description' => 'Ta bort nätverkskartor',
        ],
    ],
    'dashboard' => [
        'title' => 'Instrumentbrädor',
        'copy' => [
            'label' => 'Kopiera instrumentpanelen',
            'description' => 'Kopiera instrumentpaneler från andra användare',
        ],
    ],
    'device-group' => [
        'title' => 'Enhetsgrupper',
        'viewAll' => [
            'label' => 'Visa alla enhetsgrupper',
            'description' => 'Visa alla enhetsgrupper',
        ],
        'view' => [
            'label' => 'Visa enhetsgrupp',
            'description' => 'Visa enhetsgrupper som innehåller enheter som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Skapa enhetsgrupper',
            'description' => 'Skapa nya enhetsgrupper',
        ],
        'update' => [
            'label' => 'Redigera enhetsgrupper',
            'description' => 'Ändra befintliga enhetsgrupper',
        ],
        'delete' => [
            'label' => 'Ta bort enhetsgrupper',
            'description' => 'Ta bort enhetsgrupper',
        ],
    ],
    'link' => [
        'title' => 'Länkar',
        'viewAll' => [
            'label' => 'Visa alla länkar',
            'description' => 'Visa information om nätverkslänkar',
        ],
    ],
    'location' => [
        'title' => 'Platser',
        'viewAll' => [
            'label' => 'Visa alla platser',
            'description' => 'Visa alla platser',
        ],
        'view' => [
            'label' => 'Visa plats',
            'description' => 'Visa plats relaterad till enheter som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Skapa platser',
            'description' => 'Skapa nya platser',
        ],
        'update' => [
            'label' => 'Redigera platser',
            'description' => 'Ändra befintliga platser',
        ],
        'delete' => [
            'label' => 'Ta bort platser',
            'description' => 'Ta bort platser',
        ],
    ],
    'mempool' => [
        'title' => 'Minnespooler',
        'update' => [
            'label' => 'Uppdatera minnespool',
            'description' => 'Uppdatera minnespooldata',
        ],
    ],
    'notification' => [
        'title' => 'Aviseringar',
        'create' => [
            'label' => 'Skapa meddelanden',
            'description' => 'Skapa nya aviseringar',
        ],
        'update' => [
            'label' => 'Redigera aviseringar',
            'description' => 'Ändra befintliga meddelanden',
        ],
    ],
    'oxidized' => [
        'title' => 'Oxiderat',
        'view' => [
            'label' => 'Visa Oxiderad',
            'description' => 'Visa säkerhetskopior av enhetskonfiguration',
        ],
        'refresh' => [
            'label' => 'Uppdatera Oxiderad',
            'description' => 'Utlösa en återhämtning av konfigurationen för en enhet',
        ],
        'search' => [
            'label' => 'Sök Oxiderad',
            'description' => 'Sök igenom säkerhetskopior av oxiderad konfiguration',
        ],
    ],
    'peering-db' => [
        'title' => 'PeeringDB',
        'view' => [
            'label' => 'Se PeeringDB',
            'description' => 'Visa PeeringDB-information',
        ],
    ],
    'plugin' => [
        'title' => 'Plugins',
        'admin' => [
            'label' => 'Plugin Admin',
            'description' => 'Hantera plugininställningar och status',
        ],
    ],
    'poller' => [
        'title' => 'Pollers',
        'view' => [
            'label' => 'Visa Pollers',
            'description' => 'Visa pollerinformation och status',
        ],
        'update' => [
            'label' => 'Redigera pollare',
            'description' => 'Ändra pollerinställningar',
        ],
        'delete' => [
            'label' => 'Ta bort Pollers',
            'description' => 'Ta bort pollers från LibreNMS',
        ],
    ],
    'poller-group' => [
        'title' => 'Pollergrupper',
        'create' => [
            'label' => 'Skapa pollargrupper',
            'description' => 'Skapa nya pollargrupper',
        ],
        'update' => [
            'label' => 'Redigera pollargrupper',
            'description' => 'Ändra befintliga pollargrupper',
        ],
        'delete' => [
            'label' => 'Ta bort pollargrupper',
            'description' => 'Ta bort pollargrupper',
        ],
    ],
    'port' => [
        'title' => 'Hamnar',
        'viewAll' => [
            'label' => 'Visa alla hamnar',
            'description' => 'Visa alla hamnar',
        ],
        'view' => [
            'label' => 'Visa portdetaljer',
            'description' => 'Visa portar för enheter eller portar som användaren kan komma åt',
        ],
        'update' => [
            'label' => 'Redigera portar',
            'description' => 'Ändra portbeskrivningar och inställningar',
        ],
        'delete' => [
            'label' => 'Ta bort portar',
            'description' => 'Ta bort portar och deras data permanent',
        ],
    ],
    'port-group' => [
        'title' => 'Hamngrupper',
        'viewAll' => [
            'label' => 'Visa alla hamngrupper',
            'description' => 'Visa alla portgrupper',
        ],
        'view' => [
            'label' => 'Visa hamngrupp',
            'description' => 'Visa portgrupper som innehåller portar som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Skapa portgrupper',
            'description' => 'Skapa nya portgrupper',
        ],
        'update' => [
            'label' => 'Redigera portgrupper',
            'description' => 'Ändra befintliga portgrupper',
        ],
        'delete' => [
            'label' => 'Ta bort portgrupper',
            'description' => 'Ta bort portgrupper',
        ],
    ],
    'processor' => [
        'title' => 'Processorer',
        'viewAll' => [
            'label' => 'Visa alla processorer',
            'description' => 'Visa alla processorer',
        ],
        'view' => [
            'label' => 'Visa processor',
            'description' => 'Visa processorer för enheter som användaren kan komma åt',
        ],
        'update' => [
            'label' => 'Uppdatera processor',
            'description' => 'Uppdatera processordata',
        ],
    ],
    'reporting' => [
        'title' => 'Rapportering',
        'update' => [
            'label' => 'Uppdatera rapportering',
            'description' => 'Uppdatera rapporteringsinställningar',
        ],
    ],
    'role' => [
        'title' => 'Roller',
        'update' => [
            'label' => 'Redigera roller',
            'description' => 'Ändra rollbehörigheter och inställningar',
        ],
    ],
    'routing' => [
        'title' => 'Routing',
        'viewAll' => [
            'label' => 'Visa alla routing',
            'description' => 'Visa all ruttinformation',
        ],
        'view' => [
            'label' => 'Visa routing',
            'description' => 'Visa specifika ruttdetaljer',
        ],
        'update' => [
            'label' => 'Uppdatera routing',
            'description' => 'Uppdatera routingdata',
        ],
    ],
    'service' => [
        'title' => 'Tjänster',
        'viewAll' => [
            'label' => 'Visa alla tjänster',
            'description' => 'Se alla tjänster',
        ],
        'view' => [
            'label' => 'Visa tjänster',
            'description' => 'Visa tjänst för enheter som användaren kan komma åt',
        ],
        'create' => [
            'label' => 'Lägg till tjänster',
            'description' => 'Lägg till nya tjänster till enheter',
        ],
        'update' => [
            'label' => 'Redigera tjänster',
            'description' => 'Ändra inställningar för servicekontroll',
        ],
        'delete' => [
            'label' => 'Ta bort tjänster',
            'description' => 'Ta bort tjänster från enheter',
        ],
    ],
    'service-template' => [
        'title' => 'Servicemallar',
        'view' => [
            'label' => 'Visa servicemallar',
            'description' => 'Visa servicemallar',
        ],
        'create' => [
            'label' => 'Skapa servicemallar',
            'description' => 'Skapa nya servicemallar',
        ],
        'update' => [
            'label' => 'Redigera servicemallar',
            'description' => 'Ändra befintliga servicemallar',
        ],
        'delete' => [
            'label' => 'Ta bort tjänstmallar',
            'description' => 'Ta bort tjänstmallar',
        ],
    ],
    'settings' => [
        'title' => 'Inställningar',
        'view' => [
            'label' => 'Visa inställningar',
            'description' => 'Visa globala LibreNMS-inställningar',
        ],
        'update' => [
            'label' => 'Redigera inställningar',
            'description' => 'Ändra globala LibreNMS-inställningar',
        ],
    ],
    'syslog' => [
        'title' => 'Syslog',
        'delete' => [
            'label' => 'Ta bort Syslog',
            'description' => 'Ta bort sysloghistorik',
        ],
    ],
    'user' => [
        'title' => 'Användare',
        'view' => [
            'label' => 'Visa användare',
            'description' => 'Visa användarkontodetaljer',
        ],
        'create' => [
            'label' => 'Skapa användare',
            'description' => 'Skapa nya användarkonton',
        ],
        'update' => [
            'label' => 'Redigera användare',
            'description' => 'Ändra användarkonton, roller och behörigheter',
        ],
        'delete' => [
            'label' => 'Ta bort användare',
            'description' => 'Ta bort användarkonton',
        ],
        'manage' => [
            'label' => 'Hantera behörigheter',
            'description' => 'Hantera användarbehörigheter',
        ],
        'updatePassword' => [
            'label' => 'Uppdatera lösenord',
            'description' => 'Uppdatera användarlösenord',
        ],
    ],
    'vlan' => [
        'title' => 'VLAN',
        'viewAll' => [
            'label' => 'Visa alla VLAN',
            'description' => 'Visa all VLAN-information',
        ],
    ],
    'vminfo' => [
        'title' => 'Virtuella maskiner',
        'viewAll' => [
            'label' => 'Visa alla virtuella maskiner',
            'description' => 'Visa all information om virtuell maskin',
        ],
        'view' => [
            'label' => 'Visa virtuell maskin',
            'description' => 'Visa information om virtuell maskin för enheter som användaren kan komma åt',
        ],
        'update' => [
            'label' => 'Uppdatera virtuell maskin',
            'description' => 'Uppdatera virtuell maskindata',
        ],
    ],
    'wireless-sensor' => [
        'title' => 'Trådlösa sensorer',
        'update' => [
            'label' => 'Uppdatera trådlös sensor',
            'description' => 'Uppdatera trådlösa sensordata',
        ],
        'delete' => [
            'label' => 'Ta bort trådlös sensor',
            'description' => 'Radera trådlösa sensordata',
        ],
    ],
    'customoid' => [
        'title' => 'Anpassade OID',
        'view' => [
            'label' => 'Visa anpassade OID',
            'description' => 'Visa anpassade OID-data',
        ],
        'create' => [
            'label' => 'Skapa anpassade OID',
            'description' => 'Skapa nya anpassade OID',
        ],
        'update' => [
            'label' => 'Redigera anpassade OID',
            'description' => 'Ändra befintliga anpassade OID',
        ],
        'delete' => [
            'label' => 'Ta bort anpassade OID',
            'description' => 'Ta bort anpassade OID',
        ],
    ],
    'rbac' => [
        'title' => 'Roller och behörigheter',
        'beta_warning_title' => 'Betafunktion',
        'beta_warning_message' => 'Detta är en betafunktion. Behörigheterna kanske inte tillämpas korrekt ännu. Rapportera eventuella problem du stöter på.',
        'manage_users' => 'Hantera användare',
        'manage_roles' => 'Hantera roller',
        'add_role' => 'Lägg till roll',
        'create_role' => 'Skapa roll',
        'create_new_role' => 'Skapa ny roll',
        'edit_role' => 'Redigera roll',
        'delete_role' => 'Ta bort roll',
        'role_name' => 'Rollnamn',
        'permissions' => 'Behörigheter',
        'actions' => 'Åtgärder',
        'all_permissions' => 'Alla behörigheter',
        'view_all_permissions' => 'Visa alla behörigheter',
        'view_permissions' => 'Visa behörigheter',
        'no_permissions' => 'Inga behörigheter tilldelade',
        'confirm_delete' => 'Är du säker på att du vill ta bort den här rollen?',
        'role_name_placeholder' => 't.ex. nätverkstekniker',
        'search_permissions' => 'Sökbehörigheter...',
        'select_all' => 'Välj Alla',
        'clear_all' => 'Rensa alla',
        'save_role' => 'Spara roll',
        'update_role' => 'Uppdatera roll',
        'created' => 'Rollen :name skapades framgångsrikt',
        'updated' => 'Rollen :name har uppdaterats',
        'deleted' => 'Rollen :name har tagits bort',
        'role_name_regex' => 'Rollnamn får bara innehålla små bokstäver och bindestreck (-).',
    ],
    'permissions' => [
        'user_permissons' => ':name Behörigheter',
        'bill_access' => 'Faktureringsåtkomst (:count)',
        'device_access' => 'Enhetsåtkomst (:count)',
        'device_group_access' => 'Åtkomst till enhetsgrupp (:count)',
        'port_access' => 'Portåtkomst (:count)',
        'bill_all' => 'Alla räkningar',
        'device_all' => 'Alla enheter',
        'device_group_all' => 'Alla enhetsgrupper',
        'port_all' => 'Alla hamnar',
        'none_configured' => 'Ingen konfigurerad',
    ],
];
