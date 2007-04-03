//\/////
//\  overLIB Adaptive_Width Plugin
//\
//\  You may not remove or change this notice.
//\  Copyright Erik Bosrup 1998-2003. All rights reserved.
//\  Contributors are listed on the homepage.
//\  See http://www.bosrup.com/web/overlib/ for details.
//\/////
////////
// PRE-INIT
// Ignore these lines, configuration is below.
////////
if (typeof olInfo == 'undefined' || typeof olInfo.meets == 'undefined' || !olInfo.meets(4.14)) alert('overLIB 4.14 or later is required for the Adaptive_Width Plugin.');
else {
registerCommands('adaptive_width');
////////
// DEFAULT CONFIGURATION
// You don't have to change anything here if you don't want to. All of this can be
// changed on your html page or through an overLIB call.
////////
// Default value for adaptive_width -- 300 px minimum width, 720 px maximum width
// 4 if dividing factor for text length, 9 multiplying factor for caption text
// if want to change just one value and keep the others at these defaults specifiy
// a zero (0)
var olAWDefault='300,720,4,9';
if (typeof ol_adaptive_width=='undefined') var ol_adaptive_width='';
////////
// END OF CONFIGURATION
// Don't change anything below this line, all configuration is above.
////////
////////
// INIT
////////
// Runtime variables init. Don't change for config!
var o3_adaptive_width='';
////////
// PLUGIN FUNCTIONS
////////
function setAdaptiveWidthVariables() {
	o3_adaptive_width=ol_adaptive_width;
}
// Parses Shadow and Scroll commands
function parseAdaptiveWidthExtras(pf,i,ar) {
	var k=i;
	if (k < ar.length) {
		if (ar[k]==ADAPTIVE_WIDTH) { k=getAWArgs(++k,ar,(pf+'adaptive_width')); adjustAWSettings(pf+'adaptive_width'); return k; }
	}
	return -1;
}
// Function to scan command for multiple arguments for ADAPTIVE_WIDTH
function getAWArgs(i, args, parameter) {
  var k=i,l,re,pV,str='';
  for(k=i; k<args.length; k++) {
		if(typeof args[k]=='number'&&args[k]>pmStart) break;
		str += args[k] + ',';
	}
	if (str) str=str.replace(/,$/,'');
	k--;  // reduce by one so the for loop this is in works correctly
	pV=(olNs4&&/cellpad/i.test(parameter)) ? str.split(',')[0] : str;
	eval(parameter+'="' + pV + '"');
	return k;
}
function adjustAWSettings(pmStr) {
	if(/'ol_'/.test(pmStr)) {
		if(!eval(pmStr)) return;
		else olAWDefault = setAWarr(eval(pmStr)).join(',');
	} else if(!eval(pmStr)) eval(pmStr + '="' + olAWDefault + '"');
}
function checkAdaptiveWidth() {
	if (o3_adaptive_width) {
		if (o3_wrap) clearWrapSettings();
		o3_width = dynamicSizer(o3_text, o3_cap, setAWarr(o3_adaptive_width));
	}
	return true;		
}
// sets Adaptive Width array, using default settings for any zero values
function setAWarr(vArrStr){
	var tmpArr=new Array(), dfArr=olAWDefault.split(','), awArr=vArrStr.split(',');
	for (var i=0; i<dfArr.length; i++) tmpArr[tmpArr.length++]=(i<awArr.length&&awArr[i]) ? awArr[i] : dfArr[i];
	return tmpArr;
}
// function adapted from Dennis Sandow
function dynamicSizer(aText,aCap,awArr){
  var textWide=Math.floor( parseInt(awArr[0])+aText.length/awArr[2] );
  if (aCap) textWide=Math.max( textWide, Math.floor( aCap.length*awArr[3] ) );
  return Math.min( parseInt(awArr[1]),textWide );
}
// function to undo any wrap commands
function clearWrapSettings() {
	nbspCleanup();
	o3_wrap=0;
}
////////
// PLUGIN REGISTRATIONS
////////
registerRunTimeFunction(setAdaptiveWidthVariables);
registerCmdLineFunction(parseAdaptiveWidthExtras);
registerPostParseFunction(checkAdaptiveWidth);
}
//end 
