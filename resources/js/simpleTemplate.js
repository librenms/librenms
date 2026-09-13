/**
 * SimpleTemplate - Client-side variable substitution template matching App\View\SimpleTemplate
 */

export function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function parseArgumentValue(value) {
    value = value.trim();
    if (value.length >= 2 && ((value.startsWith("'") && value.endsWith("'")) || (value.startsWith('"') && value.endsWith('"')))) {
        return value.slice(1, -1);
    }
    if (value === 'true') return true;
    if (value === 'false') return false;
    if (value === 'null') return null;
    if (!isNaN(value) && value !== '') return Number(value);
    return value;
}

export function parseArguments(argsString) {
    const args = [];
    let current = '';
    let inQuotes = false;
    let quoteChar = null;
    let depth = 0;

    for (let i = 0, len = argsString.length; i < len; i++) {
        const char = argsString[i];

        if (inQuotes) {
            if (char === quoteChar) {
                inQuotes = false;
                quoteChar = null;
            }
            current += char;
            continue;
        }

        if (char === '"' || char === "'") {
            inQuotes = true;
            quoteChar = char;
            current += char;
            continue;
        }

        if (char === '(') {
            depth++;
        } else if (char === ')') {
            depth--;
        }

        if (char === ',' && depth === 0) {
            args.push(parseArgumentValue(current));
            current = '';
            continue;
        }

        current += char;
    }

    if (current.trim() !== '') {
        args.push(parseArgumentValue(current));
    }

    return args;
}

export function executeFilter(value, filterName, args = []) {
    const str = String(value ?? '');

    switch (filterName) {
        case 'trim':
            if (args.length > 0 && typeof args[0] === 'string') {
                const chars = args[0].replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp(`^[${chars}]+|[${chars}]+$`, 'g');
                return str.replace(regex, '');
            }
            return str.trim();
        case 'upper':
            return str.toUpperCase();
        case 'lower':
            return str.toLowerCase();
        case 'title':
            return str.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
        case 'capitalize':
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        case 'length':
            return String(str.length);
        case 'replace':
            if (args.length >= 2) {
                return str.split(args[0]).join(args[1]);
            }
            return str;
        case 'slice': {
            const start = parseInt(args[0], 10) || 0;
            const length = args.length > 1 ? parseInt(args[1], 10) : undefined;
            return length !== undefined ? str.substring(start, start + length) : str.substring(start);
        }
        case 'default':
            return str === '' && args[0] !== undefined ? String(args[0]) : str;
        case 'abs':
            return String(Math.abs(parseFloat(str) || 0));
        case 'round': {
            const decimals = parseInt(args[0], 10) || 0;
            return (parseFloat(str) || 0).toFixed(decimals);
        }
        case 'url_encode':
            return encodeURIComponent(str);
        case 'escape': {
            const strategy = args[0] || 'html';
            if (strategy === 'js') return JSON.stringify(str);
            if (strategy === 'url') return encodeURIComponent(str);
            return escapeHtml(str);
        }
        case 'striptags':
            return str.replace(/<[^>]*>/g, '');
        case 'nl2br':
            return str.replace(/\n/g, '<br />');
        default:
            return str;
    }
}

export function applyFilters(value, filterChain) {
    if (!filterChain) return value;
    const filterPattern = /([a-zA-Z_][a-zA-Z0-9_]*)(?:\(([^)]*)\))?/g;
    let match;

    while ((match = filterPattern.exec(filterChain.replace(/^\|/, ''))) !== null) {
        const filterName = match[1];
        const argsString = match[2] || '';
        const args = argsString ? parseArguments(argsString) : [];
        value = executeFilter(value, filterName, args);
    }

    return value;
}

/**
 * Parse a template string with given variables
 * @param {string} template
 * @param {Record<string, any>} variables
 * @returns {string}
 */
export function parse(template, variables = {}) {
    if (!template) return '';
    const varRegex = /\{\{\s*\$?([a-zA-Z0-9\-_.:]+)(\|[^}]+)?\s*\}\}/g;

    return template.replace(varRegex, (match, varName, filterChain) => {
        let value = Object.prototype.hasOwnProperty.call(variables, varName) ? variables[varName] : '';
        if (value === undefined || value === null) {
            value = '';
        }
        value = String(value);

        if (filterChain) {
            value = applyFilters(value, filterChain);
        }

        return value;
    });
}

/**
 * Parse a template with support for distinct placeholder styling and missing variables
 * @param {string} template
 * @param {Record<string, any>} values Known resolved values
 * @param {Record<string, string>} placeholders Placeholder variable names
 * @param {object} options
 * @returns {{ html: string, text: string, hasPlaceholders: boolean }}
 */
export function parseWithPlaceholders(template, values = {}, placeholders = {}, options = {}) {
    if (!template) return { html: '', text: '', hasPlaceholders: false };

    const tokenClass = options.tokenClass || 'tw:inline-block tw:align-baseline tw:px-1.5 tw:py-0.5 tw:rounded tw:bg-amber-100/90 tw:dark:bg-amber-900/50 tw:text-amber-800 tw:dark:text-amber-300 tw:border tw:border-dashed tw:border-amber-400 tw:dark:border-amber-600 tw:text-sm tw:font-mono tw:font-semibold tw:leading-tight';
    const varRegex = /\{\{\s*\$?([a-zA-Z0-9\-_.:]+)(\|[^}]+)?\s*\}\}/g;

    let htmlResult = '';
    let textResult = '';
    let lastIndex = 0;
    let hasPlaceholders = false;
    let match;

    while ((match = varRegex.exec(template)) !== null) {
        const matchIndex = match.index;
        const literal = template.substring(lastIndex, matchIndex);
        htmlResult += escapeHtml(literal);
        textResult += literal;
        lastIndex = varRegex.lastIndex;

        const varName = match[1];
        const filterChain = match[2];

        let val;
        let isPlaceholder = false;

        if (Object.prototype.hasOwnProperty.call(values, varName) && values[varName] !== undefined && values[varName] !== null) {
            val = values[varName];
        } else if (Object.prototype.hasOwnProperty.call(placeholders, varName)) {
            val = placeholders[varName];
            isPlaceholder = true;
            hasPlaceholders = true;
        } else {
            val = varName;
            isPlaceholder = true;
            hasPlaceholders = true;
        }

        if (filterChain) {
            val = applyFilters(String(val), filterChain);
        }

        textResult += String(val);

        if (isPlaceholder) {
            htmlResult += `<span class="${tokenClass}" title="Placeholder for unpopulated field">&lt;${escapeHtml(val)}&gt;</span>`;
        } else {
            htmlResult += escapeHtml(val);
        }
    }

    const trailing = template.substring(lastIndex);
    htmlResult += escapeHtml(trailing);
    textResult += trailing;

    return {
        html: htmlResult,
        text: textResult,
        hasPlaceholders,
    };
}

const SimpleTemplate = {
    parse,
    parseWithPlaceholders,
    applyFilters,
    executeFilter,
    parseArguments,
    parseArgumentValue,
    escapeHtml,
};

export default SimpleTemplate;
