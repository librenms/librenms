import popup from './popup.js'; // Adjust path to your popup module

export default function deviceLink(options = {}) {
    // Get the current element's href
    const href = this.$el.href || this.$el.getAttribute('href');

    if (!href) {
        console.warn('deviceLink: No href found on element');
        return popup.call(this, '', options);
    }

    // Parse the href to extract the device ID and transform the URL
    const transformDeviceUrl = (href) => {
        try {
            const url = new URL(href, window.location.origin);
            const pathParts = url.pathname.split('/').filter(part => part !== '');

            // Find the device segment and extract ID
            const deviceIndex = pathParts.findIndex(part => part === 'device');

            if (deviceIndex === -1 || deviceIndex + 1 >= pathParts.length) {
                console.warn('deviceLink: Invalid device URL format');
                return href; // Return original if parsing fails
            }

            const deviceId = pathParts[deviceIndex + 1];
            const prefixParts = pathParts.slice(0, deviceIndex);

            // Construct the new URL without ajax segment, avoiding double slashes
            const newPath = (prefixParts.length > 0 ? '/' + prefixParts.join('/') : '') + `/device/${deviceId}/popup`;

            return url.origin + newPath + url.search + url.hash;
        } catch (error) {
            console.warn('deviceLink: Error parsing URL:', error);
            return href; // Return original if parsing fails
        }
    };

    const transformedUrl = transformDeviceUrl(href);

    // Return the popup component with the transformed URL
    return popup.call(this, transformedUrl, options);
}
