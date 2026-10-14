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

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

// Render each line as its own block with its number, so numbers stay aligned when a line wraps.
// Token spans that cross a line break are closed at the end of the line and reopened on the next.
function renderLines(element, html, pre) {
    const startLine = parseInt(pre?.getAttribute("data-start"), 10) || 1;
    const lines = html.replace(/\n$/, "").split("\n");
    const digits = Math.max(3, String(startLine + lines.length - 1).length);
    const numberWidth = Number((digits * 0.9).toFixed(2));
    const gutter = Number((numberWidth + 0.9).toFixed(2));

    if (pre) {
        pre.style.paddingLeft = `${gutter}em`;
        pre.style.setProperty("--line-number-width", `${numberWidth}em`);
        pre.style.setProperty("--line-number-gutter", `${gutter}em`);
    }

    const open = [];
    // One x-ignore wrapper so Alpine's MutationObserver sees a single node instead of walking every line
    element.innerHTML = '<span class="lines" x-ignore>' + lines.map((line, i) => {
        const reopen = open.join("");
        for (const [tag] of line.matchAll(/<\/?span[^>]*>/g)) {
            if (tag[1] === "/") {
                open.pop();
            } else {
                open.push(tag);
            }
        }

        return `<span class="line"><span class="line-number" aria-hidden="true">${startLine + i}</span>${reopen}${line}${"</span>".repeat(open.length)}\n</span>`;
    }).join("") + '</span><span class="line-numbers-rows" aria-hidden="true"></span>';
}

function getWorker() {
    if (!worker) {
        worker = new Worker(new URL("./configHighlight.worker.js", import.meta.url), {
            type: "module",
        });

        worker.onmessage = function (e) {
            const { id, html } = e.data;
            if (id === currentJobId && activeElement) {
                renderLines(activeElement, html, activeElement.parentElement);
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
    renderLines(element, escapeHtml(rawContent), pre);
    activeElement = element;

    // Cancel / supercede any ongoing tokenization job
    const jobId = ++currentJobId;

    if (rawContent) {
        getWorker().postMessage({
            id: jobId,
            content: rawContent,
            language,
        });
    }
}
