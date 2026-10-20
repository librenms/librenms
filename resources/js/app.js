/**
 * First we will load all of this project's JavaScript dependencies.
 */

import "../css/app.css";
import "./bootstrap";

// Gridstack (bundled by Vite)
import "gridstack/dist/gridstack.min.css";
import { GridStack } from "gridstack";

// // Alpine Components
import Alpine from "alpinejs";
import intersect from "@alpinejs/intersect";
import collapse from "@alpinejs/collapse";
import sort from "@alpinejs/sort";
import tooltip from "./components/alpine/tooltip.js";
// import popup from './components/alpine/popup.js'
import popup from "./components/alpine/oldpopup.js";
import deviceLink from "./components/alpine/deviceLink.js";
import portLink from "./components/alpine/portLink.js";
import filterBarComponent from "./components/alpine/filterBarComponent.js";
import remoteDropdown from "./components/alpine/remoteDropdown.js";
import dateRangePicker from "./components/alpine/dateRangePicker.js";
import { librenmsSelect, librenmsSetting, settingArray, settingArrayDynamic, settingArraySubKeyed, settingGroupRoleMap, settingOxidizedMaps, settingsPage } from "./components/alpine/settings.js";
import LibreNMSDate from "./datetime.js";
import LibreNMSUrl from "./url.js";
import LibreNMSNumber from "./number.js";

window.GridStack = GridStack;

window.LibreNMS = window.LibreNMS || {};
window.LibreNMS.Date = LibreNMSDate;
window.LibreNMS.Url = LibreNMSUrl;
window.LibreNMS.Number = LibreNMSNumber;

Alpine.plugin(intersect);
Alpine.plugin(collapse);
Alpine.plugin(sort);
Alpine.plugin(tooltip);
Alpine.data('popup', popup);
Alpine.data('deviceLink', deviceLink);
Alpine.data('portLink', portLink);
Alpine.data("filterBarComponent", filterBarComponent);
Alpine.data("remoteDropdown", remoteDropdown);
Alpine.data("dateRangePicker", dateRangePicker);
Alpine.data("settingsPage", settingsPage);
Alpine.data("librenmsSetting", librenmsSetting);
Alpine.data("librenmsSelect", librenmsSelect);
Alpine.data("settingArray", settingArray);
Alpine.data("settingArraySubKeyed", settingArraySubKeyed);
Alpine.data("settingGroupRoleMap", settingGroupRoleMap);
Alpine.data("settingOxidizedMaps", settingOxidizedMaps);
Alpine.data("settingArrayDynamic", settingArrayDynamic);

window.Alpine = Alpine;

if (document.querySelector('[data-config-backups]')) {
    window.LibreNMS.loadConfigHighlight = () => import("./configHighlight.js");
}

Alpine.start();
