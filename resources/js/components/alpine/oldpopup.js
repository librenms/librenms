export default function popup(url = "", options = {}) {
    return {
        popupShow: false,
        showTimeout: null,
        hideTimeout: null,
        ignoreNextShownEvent: false,
        delay: 300,
        show(timeout) {
            clearTimeout(this.hideTimeout);
            this.showTimeout = setTimeout(() => {
                this.popupShow = true;
                Popper.createPopper(this.$refs.targetRef, this.$refs.popupRef, {
                    padding: 8
                });

                // close other popups, except this one
                this.ignoreNextShownEvent = true;
                this.$dispatch("librenms-popup-shown", this.$el);
            }, timeout);
        },
        hide(timeout) {
            if (this.ignoreNextShownEvent) {
                this.ignoreNextShownEvent = false;
                return;
            }

            clearTimeout(this.showTimeout);
            this.hideTimeout = setTimeout(() => this.popupShow = false, timeout);
        }
    };
}
