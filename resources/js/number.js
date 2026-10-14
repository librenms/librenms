// Mirrors LibreNMS\Util\Number so values look the same in php and javascript

function calcRound(value, round, sf) {
    if (sf) {
        let sfround = sf;
        if (value > 1) {
            // number of digits to the left of the decimal
            const sflen = String(Math.trunc(value)).length;
            sfround = sflen >= sf ? 0 : sfround - sflen;
        }

        if (sfround < round) {
            return sfround;
        }
    }

    return round;
}

function format(value, round, sf, suffix, base, sizes) {
    value = Number(value) || 0;
    const neg = value < 0;
    value = Math.abs(value);

    let ext = sizes[0];
    for (let i = 1; i < sizes.length && value >= base; i++) {
        value /= base;
        ext = sizes[i];
    }

    const rounded = Number(value.toFixed(calcRound(value, round, sf)));

    return `${neg ? -rounded : rounded} ${ext}${suffix}`;
}

/**
 * Format a value with SI (1000 based) prefixes, for example formatSi(1500000, 2, 0, 'bps') = '1.5 Mbps'
 */
function formatSi(value, round = 2, sf = 0, suffix = 'B') {
    const abs = Math.abs(Number(value) || 0);
    if (abs !== 0 && abs < 0.1) {
        const neg = value < 0;
        const sizes = ['', 'm', 'u', 'n', 'p'];
        let small = abs;
        let ext = sizes[0];
        for (let i = 1; i < sizes.length && small <= 0.1; i++) {
            small *= 1000;
            ext = sizes[i];
        }
        const rounded = Number(small.toFixed(calcRound(small, round, sf)));

        return `${neg ? -rounded : rounded} ${ext}${suffix}`;
    }

    return format(value, round, sf, suffix, 1000, ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y']);
}

/**
 * Format a value with binary (1024 based) prefixes, for example formatBi(1536, 2, 0, 'B') = '1.5 KiB'
 */
function formatBi(value, round = 2, sf = 0, suffix = 'B') {
    return format(value, round, sf, suffix, 1024, ['', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi']);
}

/**
 * Convert an SI or binary formatted value back to a number, for example toBytes('10G') = 10000000000
 * Returns NaN for invalid strings.
 */
function toBytes(formatted) {
    const matches = String(formatted).trim().match(/^([\d.]+)\s?([kKMGTPEZY]?)(i?)([bB]\w*)?$/);
    if (! matches) {
        return NaN;
    }

    const [, number, magnitude, binary] = matches;
    const exponent = {k: 1, K: 1, M: 2, G: 3, T: 4, P: 5, E: 6, Z: 7, Y: 8}[magnitude] ?? 0;

    return Number(number) * (binary === 'i' ? 1024 : 1000) ** exponent;
}

export default {
    formatSi,
    formatBi,
    toBytes,
};
