export default function popup(url = "", options = {}) {
    return {
        popupShow: false,
        showTimeout: null,
        hideTimeout: null,
        ignoreNextShownEvent: false,
        showDelay: options.showDelay || 100,
        hideDelay: options.hideDelay || 300,
        popperInstance: null,
        show(timeout) {
            clearTimeout(this.hideTimeout);
            this.showTimeout = setTimeout(() => {
                this.popupShow = true;

                this.$nextTick(() => {
                    if (this.popperInstance) {
                        this.popperInstance.destroy();
                    }

                    if (typeof Popper !== 'undefined') {
                        this.popperInstance = new Popper(this.$refs.targetRef, this.$refs.popupRef, {
                            placement: options.placement || 'bottom',
                            positionFixed: true,
                            modifiers: {
                                offset: {
                                    offset: '0, 8'
                                },
                                preventOverflow: {
                                    boundariesElement: 'viewport',
                                    padding: 8
                                },
                                flip: {
                                    boundariesElement: 'viewport',
                                    padding: 8
                                }
                            }
                        });
                    }
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
            this.hideTimeout = setTimeout(() => {
                this.popupShow = false;
                if (this.popperInstance) {
                    this.popperInstance.destroy();
                    this.popperInstance = null;
                }
            }, timeout);
        }
    };
}
