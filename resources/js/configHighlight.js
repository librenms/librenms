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

const osLanguages = {
    aos: "cisco-config",
    aos6: "cisco-config",
    aos7: "cisco-config",
    arista_eos: "cisco-config",
    "aruba-instant": "cisco-config",
    arubaos: "cisco-config",
    "arubaos-cx": "cisco-config",
    asa: "cisco-config",
    comware: "comware-config",
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

const HIGHLIGHT_THRESHOLD = 5000;
const LINE_NUMBERS_THRESHOLD = 20000;

export default function highlightConfig(element, content) {
    const language = resolveLanguage(element.dataset.os, element.dataset.configHighlighting);

    element.classList.add(`language-${language}`);
    element.parentElement.classList.add(`language-${language}`);
    element.textContent = content ?? "";

    const pre = element.parentElement;
    const lines = content ? (content.match(/\n(?!$)/g) || []).length + 1 : 1;

    const existingRows = pre.querySelector(".line-numbers-rows");
    if (existingRows) {
        existingRows.remove();
    }

    const showLineNumbers = lines <= LINE_NUMBERS_THRESHOLD;
    const showHighlighting = lines <= HIGHLIGHT_THRESHOLD;

    if (!showLineNumbers) {
        pre.classList.remove("line-numbers");
        element.classList.remove("line-numbers");
        pre.style.paddingLeft = "";
        return;
    }

    pre.classList.add("line-numbers");
    const startLine = parseInt(pre.getAttribute("data-start"), 10) || 1;
    const maxLineNumber = startLine + lines - 1;
    const digits = Math.max(3, String(maxLineNumber).length);
    const rowsWidth = Number((digits * 0.9).toFixed(2));
    const paddingLeft = Number((rowsWidth + 0.9).toFixed(2));

    pre.style.paddingLeft = `${paddingLeft}em`;

    if (showHighlighting) {
        Prism.highlightElement(element);
    } else {
        const rows = document.createElement("span");
        rows.className = "line-numbers-rows";
        rows.setAttribute("aria-hidden", "true");
        rows.innerHTML = Array(lines + 1).join("<span></span>");
        element.appendChild(rows);
    }

    const rows = pre.querySelector(".line-numbers-rows");
    if (rows) {
        rows.style.left = `-${paddingLeft}em`;
        rows.style.width = `${rowsWidth}em`;
    }
}
