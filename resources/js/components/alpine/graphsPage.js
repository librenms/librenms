import LibreNMSDate from '../../datetime.js';

// a period is the one shown when it is within this much of it
const PERIOD_TOLERANCE = 0.05;

/**
 * Keeps the graphs page in step with the graph that is drawn in the browser, without reloading the page:
 * the period thumbnails and the date picker set the range of the graph, and the address,
 * the date picker and the highlighted thumbnail follow what the graph shows.
 *
 * @param {number|null} last seconds up to now the graph shows, null when it shows a fixed range
 */
export default function graphsPage(last) {
    return {
        last,
        // the date picker is being set to what the graph shows, which is not a new range to show
        syncing: false,

        graph() {
            return this.$root.querySelector('[x-data^="timeSeriesGraph"]')?.timeSeriesGraph;
        },

        picker() {
            return this.$root.querySelector('[x-data="dateRangePicker"]')?.dateRangePicker;
        },

        isPeriod(seconds) {
            return this.last !== null && Math.abs(this.last - seconds) <= PERIOD_TOLERANCE * seconds;
        },

        // A period thumbnail was clicked, modified clicks still open its link
        showPeriod(seconds, event) {
            if (event.ctrlKey || event.metaKey || event.shiftKey || ! this.graph()) {
                return;
            }

            event.preventDefault();
            this.graph().setRange({ last: seconds });
        },

        // A range was picked in the date picker
        picked({ start, end, relativeStartSeconds, relativeEndSeconds }) {
            if (this.syncing || ! start) {
                return;
            }

            const now = Math.floor(Date.now() / 1000);
            const range = relativeStartSeconds !== null && relativeEndSeconds === null
                ? { last: relativeStartSeconds }
                : {
                    from: relativeStartSeconds !== null ? now - relativeStartSeconds : LibreNMSDate.toUrl(start),
                    to: relativeEndSeconds !== null ? now - relativeEndSeconds : (end ? LibreNMSDate.toUrl(end) : now),
                };

            this.syncing = true;
            this.graph()?.setRange(range);
            this.syncing = false;
        },

        // The graph shows another range
        rangeChanged(range) {
            const from = 'last' in range ? LibreNMSDate.toShortOffset(range.last) : String(range.from);
            const to = 'last' in range ? null : String(range.to);
            const url = new URL(window.location.href);

            url.searchParams.set('from', from);
            if (to === null) url.searchParams.delete('to'); else url.searchParams.set('to', to);
            window.history.replaceState(window.history.state, '', url);

            this.last = range.last ?? null;

            if (! this.syncing) {
                this.syncing = true;
                this.picker()?.clear();
                this.picker()?.set(from, to);
                this.syncing = false;
            }
        },
    };
}
