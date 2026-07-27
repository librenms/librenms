import Prism from "prismjs";
import "prismjs/components/prism-markup";
import "prismjs/plugins/line-numbers/prism-line-numbers";
import "prismjs/plugins/line-numbers/prism-line-numbers.css";
import "../css/config-highlight.css";

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
        /\b(?:Gi|Fa|Te|Eth|Hu|Po|Vl|Lo|Tu|Se)\S*/i,
    ],
    boolean: /\b(?:enable|disable|enabled|disabled|permit|deny|accept|reject|allow|drop|yes|no|true|false|up|down)\b/i,
    keyword: /\b(?:interface|hostname|router|route|routing|network|address|gateway|vlan|vrf|policy|firewall|filter|access-list|prefix-list|community|neighbor|protocol|service|user|group|snmp|ntp|logging|authentication|authorization|accounting|certificate|crypto|ssh|set|delete|edit|config|commit|exit|end)\b/i,
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
        pattern: /(^|\s)\/[a-z][\w/-]*/im,
        lookbehind: true,
    },
    keyword: /\b(?:add|comment|disable|edit|enable|export|find|get|move|print|remove|set|unset)\b/i,
});

Prism.languages["set-config"] = Prism.languages.extend("network-config", {
    keyword: /\b(?:activate|commit|compare|copy|deactivate|delete|discard|load|merge|rename|rollback|run|save|set|show)\b/i,
});

const osLanguages = {
    aos: "cisco-config",
    aos6: "cisco-config",
    aos7: "cisco-config",
    arista_eos: "cisco-config",
    "aruba-instant": "cisco-config",
    arubaos: "cisco-config",
    "arubaos-cx": "cisco-config",
    asa: "cisco-config",
    dnos: "cisco-config",
    edgeos: "set-config",
    fortigate: "fortios-config",
    fortios: "fortios-config",
    ftos: "cisco-config",
    ios: "cisco-config",
    iosxe: "cisco-config",
    iosxr: "cisco-config",
    junos: "junos-config",
    nxos: "cisco-config",
    panos: "set-config",
    powerconnect: "cisco-config",
    procurve: "cisco-config",
    routeros: "routeros-config",
    vyos: "set-config",
    vrp: "network-config",
};

const languageAliases = {
    ios: "cisco-config",
    xml: "markup",
};

function resolveLanguage(os, configuredLanguage) {
    const explicitLanguage = languageAliases[configuredLanguage] ?? configuredLanguage;

    if (explicitLanguage && Prism.languages[explicitLanguage]) {
        return explicitLanguage;
    }

    if (osLanguages[os]) {
        return osLanguages[os];
    }

    return "network-config";
}

export default function highlightConfig(element, content) {
    const language = resolveLanguage(element.dataset.os, element.dataset.configHighlighting);

    element.classList.add(`language-${language}`);
    element.parentElement.classList.add(`language-${language}`);
    element.textContent = content ?? "";
    Prism.highlightElement(element);
}
