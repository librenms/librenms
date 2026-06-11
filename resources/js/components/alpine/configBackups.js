import LibreNMSDate from "../../datetime.js";

export default function configBackups(config) {
    return {
        backups: (config.backups || []).map((b) => ({ ...b, page: 0 })),
        page: 0,
        totalPages: config.totalPages || 0,
        total: config.total || 0,
        urls: config.urls || {},
        messages: config.messages || {},

        selected: config.latest || null,
        content: config.latest ? config.latest.content : null,
        loading: false,
        loadingMore: false,
        error: null,

        diffMode: false,
        diffSelection: [],
        diffGroups: null,

        get hasMore() {
            return this.page < this.totalPages - 1;
        },

        get diffReady() {
            return this.diffGroups !== null && this.diffSelection.length === 2;
        },

        errorMessage() {
            return this.messages[this.error] || this.messages.request_failed || this.error;
        },

        formatDate(ts) {
            return ts ? LibreNMSDate.display(ts) : '';
        },

        isSelected(backup) {
            if (this.diffMode) {
                return this.diffSelection.some((b) => b.id === backup.id);
            }

            return this.selected && this.selected.id === backup.id;
        },

        selectBackup(backup) {
            if (this.diffMode) {
                this.toggleDiffSelect(backup);
                return;
            }

            this.selected = backup;
            this.error = null;

            if (backup.type !== 'TEXT') {
                this.content = null;
                return;
            }

            this.loading = true;
            window.axios
                .get(this.urls.backup.replace('BACKUP_ID', encodeURIComponent(backup.id)), { params: { page: backup.page } })
                .then((response) => {
                    this.content = response.data.content;
                })
                .catch((error) => {
                    this.content = null;
                    this.error = error.response?.data?.error || 'request_failed';
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        loadMore() {
            this.loadingMore = true;
            const nextPage = this.page + 1;

            window.axios
                .get(this.urls.backups, { params: { page: nextPage } })
                .then((response) => {
                    this.backups.push(...response.data.backups.map((b) => ({ ...b, page: nextPage })));
                    this.page = response.data.page;
                    this.totalPages = response.data.totalPages;
                    this.total = response.data.total;
                })
                .catch((error) => {
                    this.error = error.response?.data?.error || 'request_failed';
                })
                .finally(() => {
                    this.loadingMore = false;
                });
        },

        toggleDiffMode() {
            this.diffMode = !this.diffMode;
            this.diffSelection = [];
            this.diffGroups = null;
            this.error = null;
        },

        toggleDiffSelect(backup) {
            if (backup.type !== 'TEXT') {
                return;
            }

            const index = this.diffSelection.findIndex((b) => b.id === backup.id);
            if (index >= 0) {
                this.diffSelection.splice(index, 1);
                this.diffGroups = null;
            } else {
                if (this.diffSelection.length >= 2) {
                    this.diffSelection.pop();
                }
                this.diffSelection.push(backup);
            }

            if (this.diffSelection.length === 2) {
                this.loadDiff();
            }
        },

        loadDiff() {
            // oldest as the original, newest as the revision
            const [orig, rev] = [...this.diffSelection].sort((a, b) => a.date - b.date);

            this.loading = true;
            this.error = null;
            this.diffGroups = null;

            window.axios
                .get(this.urls.diff, { params: { orig: orig.id, rev: rev.id } })
                .then((response) => {
                    this.diffGroups = response.data.groups;
                })
                .catch((error) => {
                    this.error = error.response?.data?.error || 'request_failed';
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        get diffRows() {
            if (!this.diffGroups) {
                return [];
            }

            const rows = [];
            this.diffGroups.forEach((group) => {
                if (group.type === 'COMMON') {
                    group.original.forEach((line) => rows.push({ mode: 'common', line: line.line, text: line.text }));
                    return;
                }
                if (group.type === 'DELETED' || group.type === 'CHANGED') {
                    group.original.forEach((line) => rows.push({ mode: 'removed', line: line.line, text: line.text }));
                }
                if (group.type === 'INSERTED' || group.type === 'CHANGED') {
                    group.revised.forEach((line) => rows.push({ mode: 'added', line: line.line, text: line.text }));
                }
            });

            return rows;
        },
    };
}
