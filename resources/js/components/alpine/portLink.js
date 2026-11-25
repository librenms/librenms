import popup from "./popup.js";

export default function portLink(options = {}) {
    if (!options.port_id) {
        console.warn('portLink: port_id is required in options');
        return popup.call(this, '', options);
    }

    options.showDelay = options.showDelay || 300;
    const popupUrl = route('port.popup', {port: options.port_id});

    // params are handled by the popup component
    // Return the popup component with the constructed URL
    return popup.call(this, popupUrl, options);
}
