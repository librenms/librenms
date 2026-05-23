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

export default { applyNestedParamsToUrl };
