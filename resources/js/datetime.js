// resources/js/datetime.js

import { DateTime, Settings } from "luxon";

Settings.defaultZone = window.tz || 'local';

// Optional: log for debugging during development
if (import.meta.env.DEV) {
    console.debug('[LibreNMS.Date] Default timezone set to:', Settings.defaultZone.name);
}

function parse(input) {
    if (!input) return DateTime.invalid('empty');

    let dt;

    if (typeof input === 'number' || (typeof input === 'string' && !isNaN(input))) {
        // Unix timestamp seconds (from URL)
        dt = DateTime.fromSeconds(Number(input));
    } else if (typeof input === 'string') {
        // Backend: ISO with Z / offset => treat as an instant, then use the default zone for display
        if (input.includes('Z') || input.includes('+') || input.includes('-')) {
            dt = DateTime.fromISO(input);
        }
        // Naive string (datetime-local style) => interpret directly in default zone
        else if (input.includes('T')) {
            dt = DateTime.fromISO(input);
        }
        // Fallback for other string formats
        else {
            dt = DateTime.fromJSDate(new Date(input));
        }
    } else if (input instanceof Date && !isNaN(input.getTime())) {
        dt = DateTime.fromJSDate(input);
    } else if (DateTime.isDateTime(input)) {
        dt = input;
    } else {
        dt = DateTime.invalid('unsupported input type');
    }

    if (!dt.isValid) {
        console.debug('[LibreNMS.Date] Invalid input:', input);
    }

    return dt;
}

const LibreNMSDate = {
    display(input, opts = { dateStyle: 'medium', timeStyle: 'medium' }) {
        const dt = parse(input);
        return dt.isValid ? dt.toLocaleString(opts) : '—';
    },

    toPicker(input) {
        const dt = parse(input);
        return dt.isValid ? dt.toFormat("yyyy-MM-dd'T'HH:mm") : '';
    },

    toBackend(input) {
        const dt = parse(input);
        return dt.isValid ? dt.toUTC().toISO() : null;
    },

    toUrl(input) {
        const dt = parse(input);
        return dt.isValid ? Math.floor(dt.toSeconds()) : null;
    },

    now() {
        return DateTime.now();
    },

    zone() {
        return Settings.defaultZone.name;
    },

    parse,
};

export default LibreNMSDate;
