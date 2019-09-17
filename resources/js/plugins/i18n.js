/*
 * i18n.js
 *
 * Load vue.js i18n support
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

import Vue from 'vue'
import VueI18n from 'vue-i18n'
// import en from '../lang/en.js'
import messages from '../vue-i18n-locales.generated.js';

Vue.use(VueI18n);

export const i18n = new VueI18n({
    locale: document.querySelector('html').getAttribute('lang'),
    fallbackLocale: 'en',
    silentFallbackWarn: true,
    messages: messages,
});

// re-enable after vue-i8ln-generator is working for split locales
/*
const loadedLanguages = ['en']; // our default language that is preloaded

function setI18nLanguage (lang) {
    i18n.locale = lang
    axios.defaults.headers.common['Accept-Language'] = lang
    document.querySelector('html').setAttribute('lang', lang)
    return lang
}

export function loadLanguageAsync(lang) {
    // If the same language
    if (i18n.locale === lang) {
        return Promise.resolve(setI18nLanguage(lang))
    }

    // If the language was already loaded
    if (loadedLanguages.includes(lang)) {
        return Promise.resolve(setI18nLanguage(lang))
    }

    // If the language hasn't been loaded yet
    return import(`../lang/${lang}.js`).then(
        messages => {
            i18n.setLocaleMessage(lang, messages.default)
            loadedLanguages.push(lang)
            return setI18nLanguage(lang)
        }
    )
}
*/
