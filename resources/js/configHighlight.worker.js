import "./disablePrismWorker.js";
import Prism from "prismjs";
import "prismjs/components/prism-markup";

Prism.languages["network-config"] = {
    comment: [
        {
            pattern: /^[ \t]*[!#;].*$/m,
            greedy: true,
        },
        {
            pattern: /(^|[ \t])\/\/.*$/m,
            lookbehind: true,
            greedy: true,
        },
    ],
    string: {
        pattern: /(^|[=\s])(["'])(?:\\.|(?!\2)[^\\\r\n])*\2/m,
        lookbehind: true,
        greedy: true,
    },
    variable: [
        /\b(?:[a-f\d]{0,4}:){2,7}[a-f\d]{0,4}(?:\/\d{1,3})?\b/i,
        /\b(?:\d{1,3}\.){3}\d{1,3}(?:\/\d{1,2})?\b/,
        /\b(?:[a-f\d]{2}:){5}[a-f\d]{2}\b/i,
        /\b(?:Gi|Fa|Te|Eth|Hu|Po|Vl|Lo|Tu|Se)[\w./:-]*\d[\w./:-]*\b/i,
    ],
    boolean: /\b(?:enable|disable|enabled|disabled|permit|deny|accept|reject|allow|drop|yes|no|true|false|up|down)\b/i,
    keyword: /\b(?:set|delete|edit|commit|exit|end)\b/i,
    number: /\b(?:0x[\da-f]+|\d+(?:\.\d+)?)\b/i,
    operator: /(?:->|=>|==|!=|<=|>=|[=<>])/,
    punctuation: /[{}\[\](),:]/,
};

Prism.languages["cisco-config"] = Prism.languages.extend("network-config", {
    comment: {
        pattern: /^[ \t]*!.*$/m,
        greedy: true,
    },
    important: {
        pattern: /^end$/m,
        alias: "keyword",
    },
    keyword: /\b(?:aaa|access-class|access-group|access-list|banner|boot|class-map|clock|control-plane|crypto|default|description|enable|end|exec|hostname|interface|ip|ipv6|line|logging|mac-address-table|match|monitor|mpls|network|no|ntp|object-group|policy-map|port-channel|privilege|router|service|snmp-server|spanning-tree|switchport|username|version|vlan|vrf)\b/i,
});

Prism.languages["comware-config"] = Prism.languages.extend("network-config", {
    important: {
        pattern: /^(?:return|system-view)$/m,
        alias: "keyword",
    },
    keyword: /\b(?:acl|bgp|description|display|interface|ip|ipv6|isis|lldp|local-user|ntp-service|ospf|port|quit|radius|rip|save|shutdown|snmp-agent|ssh|stp|undo|user-interface|vlan)\b/i,
});

Prism.languages["junos-config"] = Prism.languages.extend("network-config", {
    comment: [
        {
            pattern: /(^|[ \t])\/\*[\s\S]*?\*\//,
            lookbehind: true,
            greedy: true,
        },
        {
            pattern: /(^|[ \t])[#].*$/m,
            lookbehind: true,
            greedy: true,
        },
    ],
    important: {
        pattern: /\b(?:inactive|replace):/,
        alias: "keyword",
    },
    keyword: /\b(?:apply-groups|chassis|class-of-service|delete|edit|firewall|forwarding-options|groups|interfaces|policy-options|protocols|routing-instances|routing-options|security|set|show|snmp|system)\b/i,
});

Prism.languages["fortios-config"] = Prism.languages.extend("network-config", {
    important: {
        pattern: /^\s*(?:config|edit|next|end)\b.*$/m,
        alias: "keyword",
    },
    keyword: /\b(?:append|config|edit|end|get|move|next|purge|rename|select|set|show|unset)\b/i,
});

Prism.languages["routeros-config"] = Prism.languages.extend("network-config", {
    function: {
        pattern: /(^[ \t]*)\/[a-z][\w-]*(?:[ \t]+[a-z][\w-]*)*/im,
        lookbehind: true,
    },
    keyword: [
        {
            pattern:
                /(^[ \t]*)(?:add|disable|enable|export|get|move|print|remove|set|unset)\b/im,
            lookbehind: true,
        },
        {
            pattern: /(\[\s*)find\b/i,
            lookbehind: true,
        },
    ],
    property: /\b[a-z][\w-]*(?=\s*=)/i,
});

Prism.languages["set-config"] = Prism.languages.extend("network-config", {
    keyword: /\b(?:activate|commit|compare|copy|deactivate|delete|discard|load|merge|rename|rollback|run|save|set|show)\b/i,
});

self.onmessage = function (e) {
    const { id, content, language, lines } = e.data;
    const grammar = Prism.languages[language] || Prism.languages["network-config"];
    const html = Prism.highlight(content || "", grammar, language);
    self.postMessage({ id, html, lines });
};
