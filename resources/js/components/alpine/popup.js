// Alpine.js Popup Component
export default function popup(url = '', options = {}) {
    return {
        popupShow: false,
        showTimeout: null,
        hideTimeout: null,
        ignoreNextShownEvent: false,
        delay: options.delay || 300,
        url: url,
        content: '',
        loading: false,
        error: false,
        cache: new Map(),
        popperInstance: null,
        popupElement: null,

        init() {
            // Create popup element and append to body
            this.createPopupElement();

            // Set up event listeners
            this.setupEventListeners();

            // Listen for global popup events
            window.addEventListener('librenms-popup-shown', (e) => {
                if (e.detail !== this.$el) {
                    this.hide(0);
                }
            });

            // Cleanup on destroy
            this.$el.addEventListener('alpine:destroyed', () => {
                this.destroy();
            });
        },

        createPopupElement() {
            this.popupElement = document.createElement('div');
            // this.popupElement.className = 'tw:hidden tw:bg-white tw:dark:bg-dark-gray-300 tw:dark:text-white tw:border-2 tw:border-gray-200 tw:dark:border-dark-gray-200 tw:z-50 tw:font-normal tw:leading-normal tw:text-sm tw:text-left tw:no-underline tw:rounded-lg tw:absolute tw:shadow-lg tw:max-w-sm';
            this.popupElement.className = 'tw:hidden';
            this.popupElement.style.cssText = 'max-width: 95vw; z-index: 9999;';

            // Add mouse events to popup
            this.popupElement.addEventListener('mouseenter', () => {
                clearTimeout(this.hideTimeout);
            });

            this.popupElement.addEventListener('mouseleave', () => {
                this.hide(this.delay);
            });

            document.body.appendChild(this.popupElement);
        },

        setupEventListeners() {
            // Mouse events on trigger element
            this.$el.addEventListener('mouseenter', () => {
                this.show(100);
            });

            this.$el.addEventListener('mouseleave', () => {
                this.hide(this.delay);
            });

            // Click away to close
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target) && !this.popupElement.contains(e.target)) {
                    this.hide(0);
                }
            });
        },

        async show(timeout = 0) {
            clearTimeout(this.hideTimeout);

            this.showTimeout = setTimeout(async () => {
                // Handle static content (no URL)
                if (!this.url) {
                    this.renderStaticContent();
                }
                // Load content if URL is provided and not cached
                else if (this.url && !this.content && !this.loading) {
                    await this.loadContent();
                }

                // Show popup
                this.popupShow = true;
                this.popupElement.classList.remove('tw:hidden');
                this.popupElement.classList.add('tw:block');

                // Position popup
                this.positionPopup();

                // Dispatch event to close other popups
                window.dispatchEvent(new CustomEvent('librenms-popup-shown', {
                    detail: this.$el
                }));

            }, timeout);
        },

        hide(timeout = 0) {
            clearTimeout(this.showTimeout);

            this.hideTimeout = setTimeout(() => {
                this.popupShow = false;
                this.popupElement.classList.add('tw:hidden');
                this.popupElement.classList.remove('tw:block');

                // Destroy Popper instance
                if (this.popperInstance) {
                    this.popperInstance.destroy();
                    this.popperInstance = null;
                }
            }, timeout);
        },

        async loadContent() {
            // Check cache first
            if (this.cache.has(this.url)) {
                this.content = this.cache.get(this.url);
                this.renderContent();
                return;
            }

            this.loading = true;
            this.error = false;
            this.renderLoadingState();

            try {
                const response = await fetch(this.url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html,application/json',
                        'Content-Type': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                let data;

                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                    this.content = data.html || data.content || this.formatJsonContent(data);
                } else {
                    this.content = await response.text();
                }

                // Cache the response
                this.cache.set(this.url, this.content);
                this.renderContent();

            } catch (error) {
                console.error('Popup AJAX error:', error);
                this.error = true;
                this.content = '';
                this.renderErrorState();
            } finally {
                this.loading = false;
            }
        },

        renderLoadingState() {
            this.popupElement.innerHTML = `
                <div class="tw:p-4 tw:text-center tw:min-w-32">
                    <div class="tw:animate-spin tw:w-4 tw:h-4 tw:border-2 tw:border-gray-300 tw:border-t-blue-600 tw:rounded-full tw:mx-auto"></div>
                    <span class="tw:text-xs tw:text-gray-500 tw:mt-2 tw:block">Loading...</span>
                </div>
            `;
        },

        renderErrorState() {
            this.popupElement.innerHTML = `
                <div class="tw:p-4 tw:text-red-600 tw:text-center tw:min-w-32">
                    <div class="tw:text-red-500 tw:mb-1">⚠️</div>
                    <span class="tw:text-xs">Failed to load content</span>
                </div>
            `;
        },

        renderContent() {
            if (this.content) {
                this.popupElement.innerHTML = this.content;
            }
        },

        renderStaticContent() {
            // For static content, use the element's title attribute or create default content
            const title = this.$el.getAttribute('title') || this.$el.getAttribute('data-popup-title');
            const content = this.$el.getAttribute('data-popup-content') ||
                this.$el.getAttribute('data-content') ||
                'Static popup content';

            let html = '<div class="tw:p-4">';

            if (title) {
                html += `<div class="tw:font-semibold tw:mb-2 tw:text-gray-800 tw:dark:text-white">${title}</div>`;
            }

            html += `<div class="tw:text-sm">${content}</div>`;
            html += '</div>';

            this.popupElement.innerHTML = html;
            this.content = html; // Cache it
        },

        formatJsonContent(data) {
            // Format JSON data for display if no HTML provided
            return `
                <div class="tw:p-4">
                    <pre class="tw:text-xs tw:bg-gray-100 tw:dark:bg-gray-800 tw:p-2 tw:rounded tw:overflow-auto tw:max-h-64">
                        ${JSON.stringify(data, null, 2)}
                    </pre>
                </div>
            `;
        },

        positionPopup() {
            if (!this.popupElement) return;

            // Use Popper.js if available
            if (typeof Popper !== 'undefined' && Popper.createPopper) {
                this.popperInstance = Popper.createPopper(this.$el, this.popupElement, {
                    placement: 'top',
                    modifiers: [
                        {
                            name: 'offset',
                            options: { offset: [0, 8] }
                        },
                        {
                            name: 'preventOverflow',
                            options: { padding: 8 }
                        },
                        {
                            name: 'flip',
                            options: { fallbackPlacements: ['bottom', 'right', 'left'] }
                        }
                    ]
                });
            } else {
                // Fallback manual positioning
                this.manualPosition();
            }
        },

        manualPosition() {
            const targetRect = this.$el.getBoundingClientRect();
            const popupRect = this.popupElement.getBoundingClientRect();

            let top = targetRect.top - popupRect.height - 8;
            let left = targetRect.left + (targetRect.width / 2) - (popupRect.width / 2);

            // Adjust for viewport boundaries
            if (top < 8) {
                top = targetRect.bottom + 8;
            }
            if (left < 8) {
                left = 8;
            }
            if (left + popupRect.width > window.innerWidth - 8) {
                left = window.innerWidth - popupRect.width - 8;
            }

            this.popupElement.style.top = `${top + window.scrollY}px`;
            this.popupElement.style.left = `${left}px`;
        },

        // Public methods
        refresh() {
            if (this.url) {
                this.cache.delete(this.url);
                this.content = '';
                if (this.popupShow) {
                    this.loadContent();
                }
            }
        },

        setUrl(newUrl) {
            this.url = newUrl;
            this.content = '';
        },

        // Cleanup
        destroy() {
            clearTimeout(this.showTimeout);
            clearTimeout(this.hideTimeout);

            if (this.popperInstance) {
                this.popperInstance.destroy();
                this.popperInstance = null;
            }

            if (this.popupElement && this.popupElement.parentNode) {
                this.popupElement.parentNode.removeChild(this.popupElement);
            }

            this.cache.clear();
        }
    }
}
