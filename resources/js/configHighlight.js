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

// One block per line so line numbers stay aligned when a line wraps
function renderLines(element, lines, property) {
    const fragment = document.createDocumentFragment();
    lines.forEach((line, i) => {
        const span = document.createElement("span");
        span.className = "line";
        span.dataset.line = i + 1;
        span[property] = line + "\n";
        fragment.append(span);
    });

    element.style.setProperty("--line-digits", String(lines.length).length);
    element.replaceChildren(fragment);
}

function getWorker() {
    if (!worker) {
        worker = new Worker(new URL("./configHighlight.worker.js", import.meta.url), {
            type: "module",
        });

        worker.onmessage = function (e) {
            const { id, lines } = e.data;
            if (id === currentJobId && activeElement) {
                renderLines(activeElement, lines, "innerHTML");
            }
        };
    }
    return worker;
}

export default function highlightConfig(element, content) {
    const rawContent = typeof content === "string" ? content : (content?.content ? String(content.content) : "");
    const language = resolveLanguage(element.dataset.os, element.dataset.configHighlighting);

    // Immediate display of raw text so user never waits for parsing
    const lines = rawContent.replace(/\n$/, "").split("\n");
    renderLines(element, lines, "textContent");
    activeElement = element;

    // Cancel / supercede any ongoing tokenization job
    const jobId = ++currentJobId;

    if (rawContent) {
        getWorker().postMessage({
            id: jobId,
            lines,
            language,
        });
    }
}
