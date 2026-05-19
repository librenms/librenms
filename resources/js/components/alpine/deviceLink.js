import popup from "./popup.js";

export default function deviceLink(options = {}) {
    if (!options.device_id) {
        console.warn('deviceLink: device_id is required in options');
        return popup.call(this, '', options);
    }

    const popupUrl = `${window.location.origin}/device/${options.device_id}/popup`;

    // params are handled by the popup component
    // Return the popup component with the constructed URL
    return popup.call(this, popupUrl, options);
}
