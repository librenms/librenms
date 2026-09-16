import popup from "./popup.js";

export default function portLink(options = {}) {
    if (!options.port_id) {
        console.warn('portLink: port_id is required in options');
        return popup.call(this, '', options);
    }

    options.showDelay = options.showDelay || 300;
    const popupUrl = `${window.location.origin}/port/${options.port_id}/popup`;

    // params are handled by the popup component
    // Return the popup component with the constructed URL
    return popup.call(this, popupUrl, options);
}
