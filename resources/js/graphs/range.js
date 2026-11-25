// A relative range follows the clock. Panning or zooming produces a fixed range.
export const now = () => Math.floor(Date.now() / 1000);

export function resolveRange(range, time = now()) {
    return 'last' in range ? { from: time - range.last, to: time } : range;
}

export function panRange({ from, to }, steps) {
    const shift = (to - from) * 0.25 * steps;

    return { from: from + shift, to: to + shift };
}

export function zoomRange({ from, to }, steps) {
    const middle = (from + to) / 2;
    const half = (to - from) * 0.5 ** steps / 2;

    return { from: middle - half, to: middle + half };
}

export function limitRange({ from, to }, maxSpan = Infinity) {
    const span = Math.min(Math.max(to - from, 120), maxSpan);
    const middle = (from + to) / 2;

    return { from: Math.round(middle - span / 2), to: Math.round(middle + span / 2) };
}
