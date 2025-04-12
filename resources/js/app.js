/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import '../css/app.css';
import './bootstrap';

import Vue from 'vue';
import {i18n} from "./plugins/i18n.js"; // translation
import ToggleButton from 'vue-js-toggle-button'
import VTooltip from 'v-tooltip'
import vSelect from 'vue-select'
import Multiselect from 'vue-multiselect'
import VueTabs from 'vue-nav-tabs'
import VModal from 'vue-js-modal'

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */
const components = import.meta.glob('./components/*.vue', { eager: true });
Object.entries(components).forEach(([path, component]) => {
    const name = path.split('/').pop().replace(/\.\w+$/, '');
    Vue.component(name, component.default);
});

Vue.use(ToggleButton);

Vue.use(VTooltip);

Vue.component('v-select', vSelect);

Vue.component('multiselect', Multiselect)

Vue.use(VueTabs)

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
