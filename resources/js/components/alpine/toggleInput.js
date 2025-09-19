
export default function toggleInput() {
    return {
        checked: false,

        init() {
            // Initialize checked state from the actual checkbox
            this.checked = this.$el.checked;

            // Create the toggle element
            this.createToggle();

            // Watch for changes to sync with checkbox
            this.$watch('checked', (value) => {
                this.$el.checked = value;
                this.updateToggleAppearance();
            });
        },

        createToggle() {
            // Hide the original checkbox
            this.$el.classList.add('tw:hidden');

            // Create toggle container
            const toggleContainer = document.createElement('div');
            toggleContainer.className = 'tw:relative tw:inline-flex tw:items-center tw:cursor-pointer';

            // Create toggle background (even larger)
            const toggleBg = document.createElement('div');
            toggleBg.className = 'tw:w-20 tw:h-10 tw:bg-gray-300 tw:dark:bg-gray-600 tw:rounded-full tw:shadow-inner tw:transition-colors tw:duration-200 tw:ease-in-out';

            // Create toggle handle (even larger)
            const toggleHandle = document.createElement('div');
            toggleHandle.className = 'tw:absolute tw:left-1 tw:top-1 tw:bg-white tw:dark:bg-gray-200 tw:w-8 tw:h-8 tw:rounded-full tw:shadow-lg tw:transition-transform tw:duration-200 tw:ease-in-out';

            toggleContainer.appendChild(toggleBg);
            toggleContainer.appendChild(toggleHandle);

            // Store references
            this.toggleBg = toggleBg;
            this.toggleHandle = toggleHandle;

            // Add click handler
            toggleContainer.addEventListener('click', () => {
                this.checked = !this.checked;
            });

            // Insert toggle after the checkbox
            this.$el.parentNode.insertBefore(toggleContainer, this.$el.nextSibling);

            // Initial appearance update
            this.updateToggleAppearance();
        },

        updateToggleAppearance() {
            if (this.checked) {
                // On state
                this.toggleBg.className = 'tw:w-20 tw:h-10 tw:bg-blue-500 tw:dark:bg-blue-400 tw:rounded-full tw:shadow-inner tw:transition-colors tw:duration-200 tw:ease-in-out';
                this.toggleHandle.className = 'tw:absolute tw:left-1 tw:top-1 tw:bg-white tw:dark:bg-gray-200 tw:w-8 tw:h-8 tw:rounded-full tw:shadow-lg tw:transition-transform tw:duration-200 tw:ease-in-out tw:transform tw:translate-x-10';
            } else {
                // Off state
                this.toggleBg.className = 'tw:w-20 tw:h-10 tw:bg-gray-300 tw:dark:bg-gray-600 tw:rounded-full tw:shadow-inner tw:transition-colors tw:duration-200 tw:ease-in-out';
                this.toggleHandle.className = 'tw:absolute tw:left-1 tw:top-1 tw:bg-white tw:dark:bg-gray-200 tw:w-8 tw:h-8 tw:rounded-full tw:shadow-lg tw:transition-transform tw:duration-200 tw:ease-in-out';
            }
        }
    }
}
