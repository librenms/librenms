import "../css/config-highlight.css";

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

const knownLanguages = new Set([
    "network-config",
    "cisco-config",
    "comware-config",
    "junos-config",
    "fortios-config",
    "routeros-config",
    "set-config",
    "markup",
]);

function resolveLanguage(os, configuredLanguage) {
    const explicitLanguage = languageAliases[configuredLanguage] ?? configuredLanguage;

    if (explicitLanguage && knownLanguages.has(explicitLanguage)) {
        return explicitLanguage;
    }

    if (osLanguages[os]) {
        return osLanguages[os];
    }

    return "network-config";
}

let worker = null;
let currentJobId = 0;
let activeElement = null;

function renderLineNumbers(element, lines, pre) {
    const startLine = parseInt(pre.getAttribute("data-start"), 10) || 1;
    const maxLineNumber = startLine + lines - 1;
    const digits = Math.max(3, String(maxLineNumber).length);
    const rowsWidth = Number((digits * 0.9).toFixed(2));
    const paddingLeft = Number((rowsWidth + 0.9).toFixed(2));

    pre.style.paddingLeft = `${paddingLeft}em`;

    const existingRows = element.querySelector(".line-numbers-rows");
    if (existingRows) {
        existingRows.remove();
    }

    const rows = document.createElement("span");
    rows.className = "line-numbers-rows";
    rows.setAttribute("aria-hidden", "true");
    rows.style.left = `-${paddingLeft}em`;
    rows.style.width = `${rowsWidth}em`;
    rows.innerHTML = Array(lines + 1).join("<span></span>");

    element.appendChild(rows);
}

function getWorker() {
    if (!worker) {
        worker = new Worker(new URL("./configHighlight.worker.js", import.meta.url), {
            type: "module",
        });

        worker.onmessage = function (e) {
            const { id, html, lines } = e.data;
            if (id === currentJobId && activeElement) {
                const pre = activeElement.parentElement;
                activeElement.innerHTML = html;
                if (pre) {
                    renderLineNumbers(activeElement, lines, pre);
                }
            }
        };
    }
    return worker;
}

export default function highlightConfig(element, content) {
    const rawContent = typeof content === "string" ? content : (content?.content ? String(content.content) : "");
    const language = resolveLanguage(element.dataset.os, element.dataset.configHighlighting);
    const pre = element.parentElement;

    element.className = `language-${language}`;
    if (pre) {
        pre.className = `config-highlight line-numbers language-${language} ${pre.className.split(" ").filter(c => !c.startsWith("language-") && c !== "line-numbers" && c !== "config-highlight").join(" ")}`.trim();
    }

    // Immediate display of raw text so user never waits for parsing
    element.textContent = rawContent;
    activeElement = element;

    const lines = rawContent ? (rawContent.match(/\n(?!$)/g) || []).length + 1 : 1;

    if (pre) {
        renderLineNumbers(element, lines, pre);
    }

    // Cancel / supercede any ongoing tokenization job
    const jobId = ++currentJobId;

    if (rawContent) {
        getWorker().postMessage({
            id: jobId,
            content: rawContent,
            language,
            lines,
        });
    }
}
