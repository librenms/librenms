export default function remoteDropdown({ endpoint, params, multi }) {
    return {
        endpoint,
        params,
        multi,
        options: [],
        search: "", // Starts fresh every time
        page: 1,
        hasMore: true,
        isLoading: false,

        init() {
            // No need to watch endpoint if using :key on the component
            this.fetch(true);

            // Still watch search for live filtering within the current field
            this.$watch("search", () => this.fetch(true));
        },

        async fetch(reset = false) {
            if (this.isLoading || (!this.hasMore && !reset) || !this.endpoint)
                return;

            if (reset) {
                this.page = 1;
                this.options = [];
                this.hasMore = true;
            }

            this.isLoading = true;
            try {
                const url = new URL(this.endpoint, window.location.origin);
                url.searchParams.append("term", this.search);
                url.searchParams.append("page", this.page);

                Object.entries(this.params).forEach(([k, v]) => {
                    url.searchParams.append(k, v);
                });

                const response = await fetch(url);
                const data = await response.json();

                const results = data.data || data.results || data;
                this.options = [...this.options, ...results];
                this.hasMore = !!data.next_page_url;
                this.page++;
            } catch (e) {
                console.error("Fetch failed", e);
            } finally {
                this.isLoading = false;
            }
        },

        select(opt) {
            this.$dispatch("remote-selected", {
                id: opt.id || opt,
                text: opt.text || opt,
            });
        },
    };
}
