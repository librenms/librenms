<?php

return [
    'database_connect' => [
        'title' => 'მონაცემთა ბაზასთან მიერთების შეცდომა',
    ],
    'database_inconsistent' => [
        'title' => 'მონაცემთა ბაზა არამდგრადია',
        'header' => 'მონაცემთა ბაზის დროს აღმოჩენილია მისი არამდგრადობა. გაასწორეთ მიერთების პრობლემა.',
    ],
    'dusk_unsafe' => [
        'title' => 'Dusk-ის გაშვება საწარმოო გარემოში უსაფრთხო არაა',
        'message' => 'გაუშვით ":command" რომ წაშალოთ Dusk, ან, თუ პროგრამისტი ბრძანდებით, დააყენეთ შესაბამისი APP_ENV',
    ],
    'file_write_failed' => [
        'title' => 'შეცდომა: ფაილში ჩაწერა შეუძლებელია',
        'message' => 'ჩავარდა ჩაწერა ფაილში (:file).  შეამოწმეთ წვდომები და SELinux/AppArmor, თუ ჩართულია.',
    ],
    'host_exists' => [
        'hostname_exists' => 'მოწყობილობა :hostname უკვე არსებობს',
        'ip_exists' => 'ვერ დაამატებთ ჰოსტს :hostname. თქვენი უკვე გაქვთ მოწყობილობა :existing IP-ით :ip',
        'sysname_exists' => 'უკვე გაქვთ მოწყობილობა :hostname გამეორებული sysName-ის გამო: :sysname',
    ],
    'host_unreachable' => [
        'unpingable' => 'ვერ ვპინგავ :hostname (:ip)',
        'unsnmpable' => 'ვერ დავუკავშირდი ჰოსტს :hostname, შეამოწმეთ snmp-ის დეტალები და მისი წვდომადობა',
        'unresolvable' => 'ჰოსტის სახელი IP-ს არ შეესაბამება',
        'no_reply_community' => 'SNMP :version: პასუხის გარეშე საზოგადოებით :credentials',
        'no_reply_credentials' => 'SNMP :version: პასუხის გარეშე ავტორიზაციის დეტალებით :credentials',
    ],
    'ldap_missing' => [
        'title' => 'PHP-ის LDAP-ის მხარდაჭერა აღმოჩენილი არაა',
        'message' => 'PHP-ში LDAP-ის მხარდაჭერა აღმოჩენილი არაა, დააყენეთ PHP-LDAP გაფართოება',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'გადაცილებულია მაქსიმალური გაშვების დრო :seconds წმ|გადაცილებულია მაქსიმალური გაშვების დრო :seconds წმ',
        'message' => 'გვერდის ჩატვირთვის დრო გადასცდა PHP-ის გაშვების დროს. ან გაზარდეთ max_execution_time თქვენს php.ini-ში, ან გააუმჯობესეთ სერვერის აპარატურა.',
    ],
    'unserializable_route_cache' => [
        'title' => 'შეცდომა PHP-ის არასწორი ვერსიის გამო',
        'message' => 'ვერსია, რომელიც გაშვებულია ვებსერვერში,(:web_version) არ ემთხვევა CLI-ის ვერსიას (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'მხარდაუჭერელი SNMP-ის ვერსია ":snmpver". უნდა იყოს v1, v2c, or v3',
    ],
];
