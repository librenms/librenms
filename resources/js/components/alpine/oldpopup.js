import { createPopper } from '@popperjs/core';

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

                    this.popperInstance = createPopper(this.$refs.targetRef, this.$refs.popupRef, {
                        placement: options.placement || 'bottom',
                        strategy: 'fixed',
                        modifiers: [
                            { name: 'offset', options: { offset: [0, 8] } },
                            { name: 'preventOverflow', options: { rootBoundary: 'viewport', padding: 8 } },
                            { name: 'flip', options: { rootBoundary: 'viewport', padding: 8 } },
                        ],
                    });
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
