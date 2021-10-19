/*
 * docs-settings-links.js
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

function findGetParameter(parameterName) {
    let result = null, tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}

function isValidHttpUrl(string) {
    let url;

    try {
        url = new URL(string);
    } catch (_) {
        return false;
    }

    return url.protocol === "http:" || url.protocol === "https:";
}

function promptSettingUrl(e) {
    e.preventDefault();
    let librenmsUrl = prompt("Enter your LibreNMS URL to get direct settings link.\nNote: This URL is only stored in your browser.", localStorage.getItem('librenms_url'));

    if (! isValidHttpUrl(librenmsUrl)) {
        alert("Invalid url, must start with http:// or https://")
        return false;
    }

    wrapSettingsLinks(librenmsUrl);
    return false;
}


function wrapSettingsLinks(librenmsUrl) {
    // fetch saved url
    if (! librenmsUrl) {
        librenmsUrl = localStorage.getItem('librenms_url');
    }

    if (librenmsUrl) {
        localStorage.setItem('librenms_url', librenmsUrl);
        librenmsUrl = librenmsUrl.replace(/\/+$/i, ''); // trim trailing /
        [].forEach.call(document.querySelectorAll('.admonition.setting>.admonition-title'), function (el) {
            if (! el.dataset.setting_url) {
                el.dataset.setting_url = el.innerText;
            }

            let link = document.createElement('a');
            link.classList.add('setting-link');
            link.href = librenmsUrl + '/settings/' + el.dataset.setting_url;
            link.innerText = link.href;
            link.target = '_blank';

            let edit = document.createElement('a');
            edit.classList.add('url-edit-link');
            edit.title = "Change setting base url"
            edit.onclick = promptSettingUrl;
            edit.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!-- Font Awesome Free 5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) --><path d="M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z"/></svg>'

            el.innerText = '';
            el.appendChild(link);
            el.appendChild(document.createTextNode(' '))
            el.appendChild(edit);
        });
    } else {
        [].forEach.call(document.querySelectorAll('.admonition.setting>.admonition-title'), function (el) {
            if (!el.dataset.setting_url) {
                el.dataset.setting_url = el.innerText;
            }

            let link = document.createElement('a');
            link.classList.add('setting-link');
            link.onclick = promptSettingUrl;
            link.innerText = 'https://<your librenms url>/' + el.dataset.setting_url;
            el.innerText = '';
            el.appendChild(link);
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    wrapSettingsLinks(findGetParameter('librenms_url'));
}, false);
