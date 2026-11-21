/**
 * Copy text, also where the clipboard api is missing: it is only available on https.
 *
 * @param {string} text
 * @returns {Promise<boolean>} whether it was copied
 */
function copy(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text).then(() => true, () => false);
    }

    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.setAttribute("readonly", "");
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();
    let copied;
    try {
        copied = document.execCommand("copy");
    } catch {
        copied = false;
    }
    textarea.remove();

    return Promise.resolve(copied);
}

/**
 * Copy a link and say whether it worked, a link that could not be copied is shown to copy by hand.
 *
 * @param {string} url
 * @param {{copied?: string, failed?: string}} lang
 * @returns {Promise<boolean>}
 */
function copyLink(url, { copied = "Copied", failed = "Copy failed" } = {}) {
    return copy(url).then((done) => {
        if (done) {
            toastr.success(copied);
        } else {
            toastr.error(url, failed);
        }

        return done;
    });
}

export default { copy, copyLink };
