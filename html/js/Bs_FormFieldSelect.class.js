function Bs_FormFieldSelect() {
this.hasValue = function(val) {
val = val + '';for (var i=0; i<this.length; i++) {
var t = this.options[i].value + '';if (t == val) return true;}
return false;}
this.getValue = function() {
var selIndex = this.selectedIndex;if ((selIndex != 'undefined') && (selIndex > -1)) {
if (typeof(this.options[selIndex].value) != 'undefined') return this.options[selIndex].value;if (typeof(this.options[selIndex].text)  != 'undefined') return this.options[selIndex].text;}
return 'undefined';}
this.getValueOrText = function(selIndex) {
if (typeof(selIndex) == 'undefined') selIndex = this.selectedIndex;if ((selIndex != 'undefined') && (selIndex > -1)) {
if (typeof(this.options[selIndex].value) != 'undefined') {
if (typeof(this.options[selIndex].outerHTML) == 'string') {
if (this.options[selIndex].outerHTML.toLowerCase().indexOf('value=') != -1) {
return this.options[selIndex].value;}
} else {
if (this.options[selIndex].value != '') return this.options[selIndex].value;}
}
if (typeof(this.options[selIndex].text)  != 'undefined') return this.options[selIndex].text;}
return false;}
this.getTextForValue = function(value) {
for (var i=0; i<this.options.length; i++) {
if (this.options[i].value == value) {
return this.options[i].text;}
}
return false;}
this.setTo = function(compare, type) {
if (typeof(type) == 'undefined') type = 'text';for (var i=0; i<this.length; i++) {
if (this.options[i][type] == compare) {
this.selectedIndex = i;return true;}
}
return false;}
this.moveSelectedTo = function(toField, keepSelected) {
if (typeof(toField) == 'string') toField = document.getElementById(toField);if (bs_isNull(toField)) return false;var unsetArray = new Array();for (var i=0; i<this.length; i++) {
if (this.options[i].selected) {
var newOpt = new Option(this.options[i].text, this.options[i].value, false, false);toField.options[toField.length] = newOpt;unsetArray[unsetArray.length] = i;}
}
unsetArray.reverse();for (var i=0; i<unsetArray.length; i++) {
this.options[unsetArray[i]] = null;}
return true;}
this.moveAllTo = function(toField) {
if (typeof(toField) == 'string') toField = document.getElementById(toField);if (bs_isNull(toField)) return false;var unsetArray = new Array();for (var i=0; i<this.length; i++) {
var newOpt = new Option(this.options[i].text, this.options[i].value, false, false);toField.options[toField.length] = newOpt;unsetArray[unsetArray.length] = i;}
unsetArray.reverse();for (var i=0; i<unsetArray.length; i++) {
this.options[unsetArray[i]] = null;}
return true;}
this.moveTo = function(toField, optionValue) {
if (typeof(toField) == 'string') toField = document.getElementById(toField);if (bs_isNull(toField)) return false;var unsetArray = new Array();for (var i=0; i<this.options.length; i++) {
if (this.options[i].value == optionValue) {
var newOpt = new Option(this.options[i].text, this.options[i].value, false, false);toField.options[toField.length] = newOpt;unsetArray[unsetArray.length] = i;break;}
}
unsetArray.reverse();for (var i=0; i<unsetArray.length; i++) {
this.options[unsetArray[i]] = null;}
return true;}
this.moveHashTo = function(toField, hash) {
if (typeof(toField) == 'string') toField = document.getElementById(toField);if (bs_isNull(toField)) return false;var unsetArray = new Array();for (var i=0; i<this.length; i++) {
if (typeof(hash[this.options[i].value]) != 'undefined') {
var newOpt = new Option(this.options[i].text, this.options[i].value, false, false);toField.options[toField.length] = newOpt;unsetArray[unsetArray.length] = i;}
}
unsetArray.reverse();for (var i=0; i<unsetArray.length; i++) {
this.options[unsetArray[i]] = null;}
return true;}
this.getAllKeys = function() {
var ret = new Array();for (var i=0; i<this.options.length; i++) {
ret[i] = this.options[i].value;}
return ret;}
this.getAllOptions = function() {
var ret = new Array();for (var i=0; i<this.options.length; i++) {
var key = this.getValueOrText(i);ret[key] = this.options[i].text;}
return ret;}
this.prune = function() {
this.options.length = 0;}
this.addElementsByHash = function(dataHash) {
var i = 0;for (var key in dataHash) {
var newOpt = new Option(dataHash[key], key, false, false);this.options[this.options.length] = newOpt;i++;}
return i;}
this.sortByText = function(desc, natural) {
var sortArr = new Array;for (var i=0; i<this.length; i++) {
if (this.options[i].value == 'undefined') this.options[i].value = this.options[i].text;sortArr[i] = this.options[i].text + '__BS_SORT__' + this.options[i].value;}
sortArr.sort();if (desc) sortArr.reverse();this.prune();var key = '';var txt = '';for (var i=0; i<sortArr.length; i++) {
var pos = sortArr[i].lastIndexOf('__BS_SORT__');txt = sortArr[i].substr(0, pos);key = sortArr[i].substr(pos + '__BS_SORT__'.length);var newOpt = new Option(txt, key, false, false);this.options[this.options.length] = newOpt;}
}
this.sortByKey = function() {
}
this.setText = function(value, text) {
for (var i=0; i<this.length; i++) {
if (this.options[i].value == value) {
this.options[i].text = text
return true;}
}
return false;}
this.removeElement = function(value) {
for (var i=0; i<this.length; i++) {
if (this.options[i].value == value) {
this.options[i] = null;return true;}
}
return false;}
this.init = function(formField) {
if (formField == null) return;for (var name in this) {
if (name == 'init') continue;formField[name] = this[name];}
}
}
