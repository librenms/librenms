/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */


require('./bootstrap');

window.Vue = require('vue').default;
import { i18n } from "./plugins/i18n.js"; // translation

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

import ToggleButton from 'vue-js-toggle-button'
Vue.use(ToggleButton);

import VTooltip from 'v-tooltip'
Vue.use(VTooltip);

import vSelect from 'vue-select'
Vue.component('v-select', vSelect);

import Multiselect from 'vue-multiselect'
Vue.component('multiselect', Multiselect)

import VueTabs from 'vue-nav-tabs'
Vue.use(VueTabs)

import VModal from 'vue-js-modal'
Vue.use(VModal)

// Vue.mixin({
//     methods: {
//         route: route
//     }
// });

Vue.filter('ucfirst', function (value) {
    if (!value) return '';
    value = value.toString();
    return value.charAt(0).toUpperCase() + value.slice(1)
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    i18n,
});
