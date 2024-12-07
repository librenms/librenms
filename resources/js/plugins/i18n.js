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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.use(VueI18n);

export const i18n = new VueI18n({
    locale: document.querySelector('html').getAttribute('lang'),
    fallbackLocale: 'en',
    silentFallbackWarn: true,
    silentTranslationWarn: true,
    messages: window.vuei18nLocales
});
