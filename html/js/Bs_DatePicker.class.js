if (!Bs_Objects) {var Bs_Objects = [];};function bs_dp_inputFieldBlur() {
event.srcElement.bsObj.updateByInputFieldBlur();}
function bs_dp_inputFieldChange() {
event.srcElement.bsObj.updateByInputFieldChange();}
function Bs_DatePicker(fieldName) {
this._objectId;this.fieldName = fieldName;this.jsBaseDir = '/_bsJavascript/';this.openByInit = false;this.monthLongEn = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');this.monthNumChars = 0;this.daysEn  = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');this.daysNumChars = 2;this.useSpinEditForYear	= (typeof(Bs_NumberField) != 'undefined');this.dayHeaderFontColor = '#D4D0C8';this.dayHeaderBgColor   = '#808080';this.dayFontColor       = 'black';this.dayBgColor         = 'white';this.dayFontColorActive = 'white';this.dayBgColorActive   = '#0A246A';this.dayTableBgColor    = 'white';this.dayBgColorOver     = '#FFFFE1';this.dayTableClassName;this.dateInputClassName;this.monthSelectClassName;this.yearInputClassName;this.dayHeaderClassName;this.dayClassName;this.dayTableAttributeString = 'width="100%" border="0" cellspacing="0" cellpadding="3"';this.width = 150;this.validateErrorMsgEn = "Not a valid date: '__VALUE__'. Try again or use the date picker. Valid formats are:\nAmerican mm/dd/yyyy (eg 12/31/2003)\nEuropean dd.mm.yyyy (eg 31.12.2003)\nISO yyyy-mm-dd (eg 2003-12-31)";this.validateErrorMsgDe = "Kein gültiges Datum: '__VALUE__'. Versuchen Sie es erneut oder benutzen Sie den DatePicker. Gültige Formate sind:\nAmerikanisch mm/dd/yyyy (Beispiel 12/31/2003)\nEuropäisch dd.mm.yyyy (Beispiel 31.12.2003)\nISO yyyy-mm-dd (Beispiel 2003-12-31)";this.internalDateFormat = 'iso';this.displayDateFormat = 'iso';this.dateFormat = 'iso';this.dayClassNameByWeekday = new Array();this.dayClassNameByDay = new Array();this.dateRangeLower = '1800-01-01';this.dateRangeUpper = '2100-12-31';this._currentDate  = '';this._currentYear  = 0;this._currentMonth = 0;this._currentDay   = 0;this.toggleButton;this._constructor = function() {
this._id = Bs_Objects.length;Bs_Objects[this._id] = this;this._objectId = "Bs_DatePicker_"+this._id;var btnName = this._objectId + '_tglBtn';this.toggleButton = new Bs_Button(btnName);eval(btnName + ' = this.toggleButton;');this.toggleButton.group           = 'toggleButton';this.toggleButton.imgName         = 'bs_calendar';this.toggleButton.cssClassDefault = 'bsBtnMouseOver';this.toggleButton.attachEvent('Bs_Objects['+this._id+'].toggleSelector();', 'on');this.toggleButton.attachEvent('Bs_Objects['+this._id+'].toggleSelector();', 'off');}
this.resetDate = function() {
this._currentYear  = 0;this._currentMonth = 0;this._currentDay   = 0;this.updateCurrentDate();}
this.setDateByChunks = function(year, month, day) {
var newDate = this.dateToIsoDate(year + '-' + month + '-' + day);if (newDate == false) return false;this._currentYear  = year;this._currentMonth = month;this._currentDay   = day;this.updateCurrentDate();return true;}
this.setDateByJunks = function(year, month, day) {
return this.setDateByChunks(year, month, day);}
this.setDateByIso = function(isoDate) {
if ("" == isoDate) {
this.resetDate();} else {
var newDate = this.dateToIsoDate(isoDate);if (newDate == false) return false;this._currentYear  = parseInt(newDate.substr(0, 4), 10);this._currentMonth = parseInt(newDate.substr(5, 2), 10);this._currentDay   = parseInt(newDate.substr(8, 2), 10);this.updateCurrentDate();}
return true;}
this.seedInternalWithCurrentDate = function() {
var dateNow = new Date();this._currentYear  = this.fixYear(dateNow.getYear());this._currentMonth = dateNow.getMonth() +1;this._currentDay   = dateNow.getDate();this.updateCurrentDate();}
this.fixYear = function(year) {
if (year < 100) {
year = parseInt('19' + year, 10);} else if ((year >= 100) && (year < 110)) {
year = parseInt(200 + '' + year.toString().substr(2, 1), 10);}
return year;}
this.drawInto = function(tagId) {
document.getElementById(tagId).innerHTML = this.render();if (this.useSpinEditForYear) this._convertYearToSpinEdit();return true;}
this.convertField = function(fieldId) {
var origFld = document.getElementById(fieldId);if (origFld == null) return false;origFld.maxLength = 10;if (bs_isEmpty(origFld['name'])) origFld['name'] = fieldId;this.fieldName = origFld['name'];var htmlCode = this.render(true);origFld.insertAdjacentHTML('afterEnd', htmlCode);if (this.useSpinEditForYear) this._convertYearToSpinEdit();origFld.bsObj = this;origFld.attachEvent('onblur',  bs_dp_inputFieldBlur);origFld.attachEvent('onkeyup', bs_dp_inputFieldChange);return true;}
this.render = function(noInputField) {
var ret = new Array();if (!noInputField) {
ret[ret.length] = '<input';ret[ret.length] = ' type="text"';ret[ret.length] = ' name="'    + this.fieldName + '"';ret[ret.length] = ' id="'      + this.fieldName + '"';ret[ret.length] = ' onBlur="Bs_Objects['+this._id+'].updateByInputFieldBlur();"';ret[ret.length] = ' onKeyUp="Bs_Objects['+this._id+'].updateByInputFieldChange();"';ret[ret.length] = ' size="10"';ret[ret.length] = ' maxlength="10"';ret[ret.length] = ' style="width:' + (this.width -22) + 'px;"';if (typeof(this.dateInputClassName) != 'undefined') {
ret[ret.length] = ' class="' + this.dateInputClassName + '"';}
ret[ret.length] = ' title="' + this.getCurrentDateReadable() + '"';ret[ret.length] = ' value="' + this.getCurrentDateFormatted() + '">';}
this.toggleButton.title = (this.openByInit) ? 'Hide Calendar' : 'Show Calendar';if (this.openByInit) this.toggleButton.setStatus(2);var btnHtml = this.toggleButton.render();ret[ret.length] = btnHtml;ret[ret.length] = '<div id="' + this._objectId + '_div"';ret[ret.length] = ' style="width:' + this.width + 'px; border:1px solid black;';if (!this.openByInit) {
ret[ret.length] = ' display:none;';}
ret[ret.length] = '">';ret[ret.length] = this.renderDatePicker();ret[ret.length] = '</div>';return ret.join('');}
this.renderDatePicker = function() {
var ret = new Array();if (this._currentDate.length == 0) {
this.seedInternalWithCurrentDate();}
ret[ret.length] = '<nobr>';ret[ret.length] = '<select name="' + this.fieldName + '_month"';ret[ret.length] = ' id="' + this._objectId + '_month" size="1"';ret[ret.length] = ' onChange="Bs_Objects['+this._id+'].updateByMonth();"';if (typeof(this.monthSelectClassName) != 'undefined') {
ret[ret.length] = ' class="' + this.monthSelectClassName + '"';} else {
ret[ret.length] = ' style="width:94px;"';}
ret[ret.length] = '>';var i = 1;for (var m=0; m<this.monthLongEn.length; m++) {
ret[ret.length] = '<option value="' + i + '"';if (this._currentMonth == i) ret[ret.length] = ' selected';ret[ret.length] = '>';if (this.monthNumChars > 0) {
ret[ret.length] = this.monthLongEn[m].substr(0, this.monthNumChars);} else {
ret[ret.length] = this.monthLongEn[m];}
ret[ret.length] = '</option>';i++;}
ret[ret.length] = '</select>';ret[ret.length] = '<input type="text" name="' + this.fieldName + '_year"';ret[ret.length] = ' id="' + this._objectId + '_year" value="' + this._currentYear + '"';ret[ret.length] = ' size="4" maxlength="4" onKeyUp="Bs_Objects['+this._id+'].updateByYearChange();"';ret[ret.length] = ' onBlur="Bs_Objects['+this._id+'].updateByYearBlur();"';if (typeof(this.yearInputClassName) != 'undefined') {
ret[ret.length] = ' class="' + this.yearInputClassName + '"';} else {
ret[ret.length] = ' style="width:42px;"';}
ret[ret.length] = '>';ret[ret.length] = '</nobr><br>';ret[ret.length] = '<div id="' + this._objectId + '_dayDiv">';ret[ret.length] = this.renderDayTable(this._currentYear, this._currentMonth, this._currentDay);ret[ret.length] = '</div>';return ret.join('');}
this.renderDayTable = function(currentYear, currentMonth, currentDay) {
var day            = 1;var lastDayOfMonth = this.getNumberOfDays(currentYear, currentMonth);var ret = new Array();ret[ret.length] = '<table ' + this.dayTableAttributeString;ret[ret.length] = ' bgcolor="' + this.dayTableBgColor + '"';if (typeof(this.dayTableClassName) != 'undefined') {
ret[ret.length] = ' class="' + this.dayTableClassName + '"';}
ret[ret.length] = '>';if (currentYear >= 1970) {
ret[ret.length] = '<tr>';for (var d=0; d<this.daysEn.length; d++) {
ret[ret.length] = '<td width="14%" bgcolor="' + this.dayHeaderBgColor + '" align="right">';ret[ret.length] = '<span';ret[ret.length] = ' title="' + this.daysEn[d] + '"';if (typeof(this.dayHeaderClassName) != 'undefined') {
ret[ret.length] = ' class="' + this.dayHeaderClassName + '"';ret[ret.length] = ' style="color:' + this.dayHeaderFontColor + '; cursor:default;">';} else {
ret[ret.length] = ' style="color:' + this.dayHeaderFontColor + '; cursor:default; font-family:arial; font-size:12px;">';}
if (this.daysNumChars > 0) {
ret[ret.length] = this.daysEn[d].substr(0, this.daysNumChars);} else {
ret[ret.length] = this.daysEn[d];}
ret[ret.length] = '</span>';ret[ret.length] = '</td>';}
ret[ret.length] = '</tr>';var dateObjFirst = new Date(currentYear, currentMonth-1, 1, 0, 0, 0);var weekDayFirst = dateObjFirst.getDay();if (weekDayFirst == 0) weekDayFirst = 7;} else {
var weekDayFirst = 1;}
for (var i=0; i<6; i++) {
ret[ret.length] = '<tr>';for (var j=1; j<8; j++) {
if ((day > lastDayOfMonth) || ((i == 0) && (j < weekDayFirst))) {
ret[ret.length] = '<td>&nbsp;</td>';} else {
ret[ret.length] = '<td';ret[ret.length] = ' id="' + this._objectId + '_td' + day + '"';ret[ret.length] = ' align="right"';ret[ret.length] = ' onMouseOver="Bs_Objects['+this._id+'].dayMouseOver(' + day + ');"';ret[ret.length] = ' onMouseOut="Bs_Objects['+this._id+'].dayMouseOut(' + day + ');"';ret[ret.length] = ' onClick="Bs_Objects['+this._id+'].updateByDay(' + day + ');"';if (typeof(this.dayClassName) != 'undefined') {
ret[ret.length] = ' class="' + this.dayClassName + '"';ret[ret.length] = ' style="cursor:hand; cursor:pointer;';} else {
ret[ret.length] = ' style="cursor:hand; cursor:pointer; font-family:arial; font-size:11px;';}
if (day == currentDay) {
ret[ret.length] = ' color:' + this.dayFontColorActive + '; background-color:' + this.dayBgColorActive + ';';} else {
ret[ret.length] = ' color:' + this.dayFontColor + '; background-color:' + this.dayBgColor + ';';}
ret[ret.length] = '">';ret[ret.length] = day;ret[ret.length] = '</td>';day++;}
}
ret[ret.length] = '</tr>';if (day >= (lastDayOfMonth +1)) break;}
ret[ret.length] = '</table>';return ret.join('');}
this.updateDayTable = function() {
document.getElementById(this._objectId + '_dayDiv').innerHTML = this.renderDayTable(this._currentYear, this._currentMonth, this._currentDay);}
this.getCurrentDateReadable = function() {
if (this._currentDay   == 0) return '';if (this._currentMonth == 0) return '';if (this._currentYear  == 0) return '';var ret = '';var dateObj = new Date(this._currentYear, this._currentMonth -1, this._currentDay);var weekDay = dateObj.getDay();if (weekDay == 0) weekDay = 7;ret += this.daysEn[weekDay -1] + ', '
ret += this.monthLongEn[this._currentMonth -1] + ' ';ret += this._currentDay;switch (this._currentDay) {
case 1: case 21: case 31:
ret += 'st'; break;case 2: case 22:
ret += 'nd'; break;case 3: case 23:
ret += 'rd'; break;default:
ret += 'th';}
ret += ' ' + this._currentYear;return ret;}
this.getCurrentDateFormatted = function() {
if (this._currentDay   == 0) return '';if (this._currentMonth == 0) return '';if (this._currentYear  == 0) return '';switch (this.dateFormat) {
case 'us':
var ret = '';if (this._currentMonth < 10) ret += '0';ret += this._currentMonth + '/';if (this._currentDay < 10) ret += '0';ret += this._currentDay + '/';ret += this._currentYear;return ret;break;case 'eu':
var ret = '';if (this._currentDay < 10) ret += '0';ret += this._currentDay + '.';if (this._currentMonth < 10) ret += '0';ret += this._currentMonth + '.';ret += this._currentYear;return ret;break;default:
return this._currentDate;}
}
this.updateInputField = function() {
var fld = document.getElementById(this.fieldName);if (fld) {
fld.value = this.getCurrentDateFormatted();fld.title = this.getCurrentDateReadable();}
}
this.updateByInputFieldChange = function() {
switch (window.event.keyCode) {
case 16:
case 35:
case 36:
case 37:
case 38:
case 39:
case 40:
return;}
var userVal = document.getElementById(this.fieldName).value;if ((userVal.length == 10) && (this.dateToIsoDate(userVal) != false)) {
this.updateByInputFieldBlur();}
}
this.updateByInputFieldBlur = function() {
var fld = document.getElementById(this.fieldName);var userVal = fld.value;if (userVal == this._currentDate) return;if (userVal.length > 0) {
newVal = this.dateToIsoDate(userVal);if (newVal == false) {
fld.value = '';				alert(this.validateErrorMsgEn.replace(/__VALUE__/, userVal));
return;}
this._currentDate  = newVal, 10;this._currentYear  = parseInt(newVal.substr(0, 4), 10);this._currentMonth = parseInt(newVal.substr(5, 2), 10);this._currentDay   = parseInt(newVal.substr(8, 2), 10);this.updateInputField();} else {
this.seedInternalWithCurrentDate();}
var div = document.getElementById(this._objectId + '_div');div.innerHTML = this.renderDatePicker();if (this.useSpinEditForYear) this._convertYearToSpinEdit();}
this.dateToIsoDate = function(someDate) {
if (someDate.length <  6) return false;if (someDate.length > 10) return false;if (someDate.indexOf('/') >= 0) {
var chunks = someDate.split('/');if (chunks.length != 3) return false;var day   = parseInt(chunks[1], 10);var month = parseInt(chunks[0], 10);var year  = parseInt(chunks[2], 10);} else if (someDate.indexOf('-') >= 0) {
var chunks = someDate.split('-');if (chunks.length != 3) return false;var day   = parseInt(chunks[2], 10);var month = parseInt(chunks[1], 10);var year  = parseInt(chunks[0], 10);} else if (someDate.indexOf('.') >= 0) {
var chunks = someDate.split('.');if (chunks.length != 3) return false;var day   = parseInt(chunks[0], 10);var month = parseInt(chunks[1], 10);var year  = parseInt(chunks[2], 10);} else {
return false;}
if (year < 100) {
if (year < 30) {
year += 2000;} else {
year += 1900;}
}
if (year >= 1970) {
var tDate = new Date(year, month -1, day);if (day   != tDate.getDate())               return false;if (month != (tDate.getMonth() +1))         return false;if (year  != this.fixYear(tDate.getYear())) return false;} else {
if ((day   < 1)    || (day   > 31))   return false;if ((month < 1)    || (month > 12))   return false;if ((year  < 1000) || (year  > 3000)) return false;}
if (day > 28) {
if (this.getNumberOfDays(year, month) < day) return false;}
var ret = '';ret += year + '-';if (month < 10) ret += '0';ret += month + '-';if (day < 10) ret += '0';ret += day;return ret;}
this.updateByDay = function(day) {
try {
var oldTd = document.getElementById(this._objectId + '_td' + this._currentDay);oldTd.style.backgroundColor = this.dayBgColor;oldTd.style.color           = this.dayFontColor;} catch (e) {
}
var oldTd = document.getElementById(this._objectId + '_td' + day);oldTd.style.backgroundColor = this.dayBgColorActive;oldTd.style.color           = this.dayFontColorActive;this._currentDay = parseInt(day, 10);this.updateCurrentDate();this.updateInputField();}
this.updateByMonth = function() {
var tmp = new Bs_FormFieldSelect();var monthSelect = document.getElementById(this._objectId + '_month');tmp.init(monthSelect);this._currentMonth = parseInt(monthSelect.getValue(), 10);this.updateCurrentDate();this.updateInputField();this.updateDayTable();}
this.updateByYearChange = function() {
var tmpYear = parseInt(document.getElementById(this._objectId + '_year').value, 10);if ((tmpYear < 2100) && (tmpYear > 1800)) {
this.updateByYearBlur();}
}
this.updateByYearBlur = function() {
var tmpYear = parseInt(document.getElementById(this._objectId + '_year').value, 10);this._currentYear = tmpYear;this.updateCurrentDate();this.updateInputField();this.updateDayTable();}
this.updateCurrentDate = function() {
if ((0 == this._currentYear) &&
(0 == this._currentMonth) &&
(0 == this._currentDay) ) {
this._currentDate = "";} else {
this._currentDate = this._currentYear + '-';if (this._currentMonth < 10) this._currentDate += '0';this._currentDate += this._currentMonth + '-';if (this._currentDay < 10) this._currentDate += '0';this._currentDate += this._currentDay;}
}
this.toggleSelector = function() {
var div = document.getElementById(this._objectId + '_div');if (div.style.display == 'none') {
div.style.display = 'block';var inputField = document.getElementById(this.fieldName);if (inputField.offsetLeft > div.offsetLeft) {
div.style.marginLeft = inputField.offsetLeft + 'px';}
var newChar  = '-';if (this.useSpinEditForYear) {
var objName = this._objectId + '_yObj';eval(objName + '.redraw();');}
this.toggleButton.setTitle('Hide Calendar');} else {
div.style.display = 'none';var newChar  = '+';this.toggleButton.setTitle('Show Calendar');}
}
this.dayMouseOver = function(day) {
var td = document.getElementById(this._objectId + '_td' + day);if (td.style.backgroundColor.toLowerCase() == this.dayBgColor.toLowerCase()) {
td.style.backgroundColor = this.dayBgColorOver;}
}
this.dayMouseOut = function(day) {
var td = document.getElementById(this._objectId + '_td' + day);if (td.style.backgroundColor.toLowerCase() == this.dayBgColorOver.toLowerCase()) {
td.style.backgroundColor = this.dayBgColor;}
}
this.isLeapYear = function(year) {
return (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0));}
this.getNumberOfDays = function(year, month) {
switch (month) {
case 2:
if (this.isLeapYear(year)) return 29;return 28;break;case 1:
case 3:
case 5:
case 7:
case 8:
case 10:
case 12:
return 31;break;default:
return 30;}
}
this._convertYearToSpinEdit = function() {
var objName = this._objectId + '_yObj';var myNf = new Bs_NumberField(this._objectId + '_year');eval(objName + ' = myNf;');myNf.buttonUp.imgPath   = this.jsBaseDir + 'components/numberfield/img/';myNf.buttonDown.imgPath = this.jsBaseDir + 'components/numberfield/img/';myNf.minValue = 1800;myNf.maxValue = 2300;myNf.attachEvent('onAfterChange', 'Bs_Objects['+this._id+'].updateByYearBlur();');myNf.draw();}
this._constructor();}
