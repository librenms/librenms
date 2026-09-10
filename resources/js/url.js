import LibreNMSDate from './datetime.js';

function flattenNestedParams(obj, prefix) {
    return Object.entries(obj).flatMap(([key, value]) => {
        const fullKey = `${prefix}[${key}]`;
        if (Array.isArray(value)) {
            return [[fullKey, value.join(',')]];
        } else if (value !== null && value !== undefined && value !== '' && typeof value === 'object') {
            return flattenNestedParams(value, fullKey);
        } else if (value !== null && value !== undefined && value !== '') {
            return [[fullKey, String(value)]];
        }
        return [];
    });
}

function applyNestedParamsToUrl(url, prefix, params, includeEmpty = false) {
    [...url.searchParams.keys()]
        .filter((k) => k.startsWith(`${prefix}[`) || k === prefix)
        .forEach((k) => url.searchParams.delete(k));

    const pairs = flattenNestedParams(params, prefix);

    if (pairs.length > 0) {
        pairs.forEach(([key, value]) => url.searchParams.set(key, value));
    } else if (includeEmpty) {
        url.searchParams.set(prefix, '');
    }

    return url;
}

function updateDateRange(relativeStartSeconds, relativeEndSeconds, start, end) {
    const url = new URL(window.location.href);
    const prevFrom = url.searchParams.get('from');
    const prevTo = url.searchParams.get('to');

    const fromValue = relativeStartSeconds
        ? LibreNMSDate.toShortOffset(relativeStartSeconds)
        : (start && LibreNMSDate.toUrl(start));
    const toValue = relativeEndSeconds
        ? LibreNMSDate.toShortOffset(relativeEndSeconds)
        : (end && LibreNMSDate.toUrl(end));

    if (fromValue) url.searchParams.set('from', fromValue); else url.searchParams.delete('from');
    if (toValue) url.searchParams.set('to', toValue); else url.searchParams.delete('to');

    if (url.searchParams.get('from') !== prevFrom || url.searchParams.get('to') !== prevTo) {
        window.location.href = url.toString();
    }
}

export default { applyNestedParamsToUrl, updateDateRange };
