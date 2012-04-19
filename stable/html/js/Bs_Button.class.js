if (!Bs_Objects) {var Bs_Objects = [];};function Bs_Button() {
this._id;this._objectId;this.id;this.group;this._status = 1;this.inactiveStyle = 3;this._imgPathDefault = '/_bsImages/buttons/';this.imgPath;this.imgName;this.height;this.width;this.backgroundColor;this.title;this.caption;this.action;this.cssClassDefault   = 'bsBtnDefault';this.cssClassMouseOver = 'bsBtnMouseOver';this.cssClassMouseDown = 'bsBtnMouseDown';this._buttonBar;this.actualizeFromChildren = 0;this._childrenButtonBar;this._isDragAction;this._attachedEvents = new Array;this._constructor = function() {
this._id = Bs_Objects.length;Bs_Objects[this._id] = this;this._objectId = "Bs_Button_"+this._id;}
this.attachEvent = function(fire, e) {
if (typeof(e) == 'undefined') e = 'on';if (typeof(this._attachedEvents[e]) == 'undefined') this._attachedEvents[e] = new Array;this._attachedEvents[e][this._attachedEvents[e].length] = fire;}
this.attachFireOff = function(param) {
}
this.render = function() {
var isGecko        = this._isGecko();var out            = new Array;var containerStyle = new Array;out[out.length] = '<div style="display:inline; white-space:nowrap;">';var tagType = 'div';out[out.length] = '<' + tagType;out[out.length] = ' id="' + this._getId() + '"';if (typeof(this.title) != 'undefined') {
out[out.length] = ' title="' + this.title + '"';}
out[out.length] = ' unselectable="on"';captionType = typeof(this.caption);if (captionType != 'undefined') {
containerStyle[containerStyle.length] = 'width:auto';} else {
if (typeof(this.width)  != 'undefined') containerStyle[containerStyle.length] = 'width:'  + this.width  + 'px';if (typeof(this.height) != 'undefined') containerStyle[containerStyle.length] = 'height:' + this.height + 'px';}
if (typeof(this.backgroundColor) != 'undefined') containerStyle[containerStyle.length] = 'background-color:' + this.backgroundColor;switch (this._status) {
case 0:
var filter = this._getInactiveStyleFilter();if (typeof(filter) == 'string') {
containerStyle[containerStyle.length] = 'filter:' + filter;}
case 1:
out[out.length] = ' class="' + this.cssClassDefault + '"';break;case 2:
out[out.length] = ' class="' + this.cssClassMouseDown + '"';break;}
out[out.length] = ' style="' + containerStyle.join(';') + '"';out[out.length] = ' onMouseOver="Bs_Objects['+this._id+'].mouseOver(this);"';out[out.length] = ' onMouseOut="Bs_Objects['+this._id+'].mouseOut(this);"';out[out.length] = ' onMouseDown="Bs_Objects['+this._id+'].mouseDown(this);"';out[out.length] = ' onMouseUp="Bs_Objects['+this._id+'].mouseUp(this);"';out[out.length] = '>';if (typeof(this.imgName) != 'undefined') {
var imgFullPath = '';imgFullPath += this._getImgPath();imgFullPath += this.imgName;if (this.imgName.substr(this.imgName.length -4) != '.gif') imgFullPath += '.gif';out[out.length] = '<img id="' + this._getId() + '_icon" src="' + imgFullPath + '"';if ((typeof(this.height) == 'undefined') || (this.height > 18)) out[out.length] = ' style="vertical-align:top;"';out[out.length] = '>';}
captionType = typeof(this.caption);if (captionType != 'undefined') {
if (captionType == 'string') {
out[out.length] = this.caption;} else {
out[out.length] = this.title;}
if (!isGecko) out[out.length] = '&nbsp;';}
if ((typeof(this._childrenButtonBar) != 'undefined') && (this.numberOfAttachedEvents('on') == 0)) {
this.group =  this._objectId + '_pseudoGroup';var imgFullPath = '';if (this.imgPath) imgFullPath += this._getImgPath();imgFullPath += 'small_black_arrow_down.gif';out[out.length] = '&nbsp;<img src="' + imgFullPath + '" style="vertical-align:middle;">&nbsp;';var subBarString = this._childrenButtonBar.render();subBarString = '<div id="' + this._objectId + '_childBar" class="bsBtnMouseOver" style="width:auto; height:auto; display:none; position:absolute; left:50px; top:50px;">' + subBarString + '</div>';out[out.length] = subBarString;}
out[out.length] = '</' + tagType + '>';out[out.length] = '</div>';return out.join('');}
this.drawOut = function() {
document.writeln(this.render());}
this.drawInto = function(elm) {
if (typeof(elm) == 'string') {
elm = document.getElementById(elm);}
if (elm != null) {
var x = this.render();			//x = x.replace(/<nobr>/, '');
			//x = x.replace(/<\/nobr>/, '');
			x = x.replace(/<nobr>/, '<span style="white-space: nowrap">');
			x = x.replace(/<\/nobr>/, '<\/span>');
elm.innerHTML = x;}
}
this.mouseOver = function(div) {
if (this._status == 2) return;if (this._status == 0) return;if (!this._isGecko()) {
div.className = this.cssClassMouseOver;}
this._fireEvent('over');}
this.mouseOut = function(div) {
if (this._status == 2) return;if (this._status == 0) return;if (!this._isGecko()) {
div.className = this.cssClassDefault;}
this._fireEvent('out');}
this.mouseDown = function(div) {
if (this._status == 0) return;this._isDragAction = false;div.className = this.cssClassMouseDown;}
this.mouseUp = function(div) {
if (this._status == 0) return;var doFireOn  = true;var doFireOff = false;if (this._isGecko()) {
div.className = this.cssClassDefault;} else {
div.className = this.cssClassMouseOver;}
if (typeof(this.group) != 'undefined') {
if (this._status == 2) {
this._status = 1;doFireOn  = false;doFireOff = true;} else {
div.className = this.cssClassMouseDown;this._status = 2;this._deactivateOtherGroupButtons();}
}
if (this._isDragAction) doFireOn = false;if (doFireOn) {
this._fireEvent('on');} else if (doFireOff) {
this._fireEvent('off');}
}
this.dragStart = function(div) {
if (this._status == 0) return false;this._isDragAction = true;div.className = this.cssClassMouseOver;return false;}
this._deactivateOtherGroupButtons = function() {
if (typeof(this._buttonBar) == 'undefined') return;for (var i=0; i<this._buttonBar._buttons.length; i++) {
var btnObj = this._buttonBar._buttons[i][0];if (typeof(btnObj) != 'object') continue;if ((btnObj.group == this.group)) {
if (btnObj._objectId == this._objectId) continue;btnObj._status = 1;btnDiv = document.getElementById(btnObj._getId());btnDiv.className = btnObj.cssClassDefault;}
}
}
this.setStatus = function(status) {
if (this._status == status) return;var oldStatus = this._status;this._status = status;var btnDiv = document.getElementById(this._getId());if (btnDiv != null) {
switch (status) {
case 0:
var filter = this._getInactiveStyleFilter();if (typeof(filter) == 'string') {
btnDiv.style.filter = filter;}
break;case 1:
btnDiv.className = this.cssClassDefault;break;case 2:
if (this._isGecko()) {
btnDiv.className = this.cssClassDefault;} else {
btnDiv.className = this.cssClassMouseDown;}
if (typeof(this.group) != 'undefined') {
this._deactivateOtherGroupButtons();}
break;}
}
if ((oldStatus == 0) && (this.inactiveStyle != 0)) {
btnDiv.style.filter = "";}
}
this.getStatus = function() {
return this._status;}
this.setTitle = function(title) {
var elm = document.getElementById(this._getId());if (elm != null) elm.title = title;this.title = title;}
this.setChildrenButtonBar = function(bar) {
bar._parentButton = this;this._childrenButtonBar = bar;}
this._isGecko = function() {
if (navigator.appName == "Microsoft Internet Explorer") return false;    var x = navigator.userAgent.match(/gecko/i);
return (x);return false;}
this._fireEvent = function(e) {
if ((e == 'on') && (typeof(this._buttonBar) != 'undefined') && (typeof(this._buttonBar._parentButton) != 'undefined')) {
this._buttonBar._parentButton._fireEvent('off');if ((this._buttonBar._parentButton.actualizeFromChildren == 1) || (this._buttonBar._parentButton.actualizeFromChildren == 3)) {
var elm = document.getElementById(this._buttonBar._parentButton._getId() + '_icon');var imgFullPath = '';if (this.imgPath) imgFullPath += this.imgPath;imgFullPath += this.imgName;if (this.imgName.substr(this.imgName.length -4) != '.gif') imgFullPath += '.gif';elm.src = imgFullPath;}
}
if (((e == 'on') || (e == 'off')) && (typeof(this._childrenButtonBar) != 'undefined') && (this.numberOfAttachedEvents('on') == 0)) {
var elm = document.getElementById(this._objectId + '_childBar');if (elm != null) {
if (e == 'on') {
this._buttonBar.ignoreEvents = true;var pos = getAbsolutePos(document.getElementById(this._getId()));var plusPixel = (typeof(this.height)  != 'undefined') ? parseInt(this.height) : 22;elm.style.top     = (pos.y + plusPixel) + 'px';elm.style.left    = pos.x + 'px';elm.style.display = 'block';} else {
this._buttonBar.ignoreEvents = false;elm.style.display = 'none';}
}
} else {
if (!this._attachedEvents[e]) return;for (var i=0; i<this._attachedEvents[e].length; i++) {
switch (typeof(this._attachedEvents[e][i])) {
case 'function':
this._attachedEvents[e][i](this);break;case 'string':
						//var ev = this._attachedEvents[e][i].replace(/__THIS__/, this);
eval(this._attachedEvents[e][i]);break;default:
}
}
}
}
this.numberOfAttachedEvents = function(e) {
try {
return this._attachedEvents[e].length;} catch (ex) {
return 0;}
}
this._getId = function() {
if (typeof(this.id) != 'undefined') return this.id;return this._objectId + "_container";}
this._getInactiveStyleFilter = function() {
switch (this.inactiveStyle) {
case 0:
return false;break;case 1:
return 'progid:DXImageTransform.Microsoft.BasicImage(grayScale=1)';break;case 2:
return 'progid:DXImageTransform.Microsoft.BasicImage(opacity=.3)';break;default:
return 'progid:DXImageTransform.Microsoft.BasicImage(grayScale=1) progid:DXImageTransform.Microsoft.BasicImage(opacity=.3)';}
}
this._getImgPath = function() {
if (typeof(this.imgPath) != 'undefined') {
return this.imgPath;} else if (typeof(this._buttonBar) != 'undefined') {
return this._buttonBar.imgPath;} else {
return this._imgPathDefault;}
}
this._constructor();}
