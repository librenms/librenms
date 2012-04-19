//\/////
//\  overLIB Center Popup Plugin
//\  This file requires overLIB 4.10 or later.
//\
//\  overLIB 4.10 - You may not remove or change this notice.
//\  Copyright Erik Bosrup 1998-2003. All rights reserved.
//\  Contributors are listed on the homepage.
//\  See http://www.bosrup.com/web/overlib/ for details.
//\/////
//\  THIS IS A VERY MODIFIED VERSION. DO NOT EDIT OR PUBLISH. GET THE ORIGINAL!
if(typeof olInfo=='undefined'||typeof olInfo.meets=='undefined'||!olInfo.meets(4.10))alert('overLIB 4.10 or later is required for the Center Popup Plugin.');else{registerCommands('centerpopup,centeroffset');
if(typeof ol_centerpopup=='undefined')var ol_centerpopup=0;if(typeof ol_centeroffset=='undefined')var ol_centeroffset='0';
var o3_centerpopup=0,o3_centeroffset='0';
function setCenterPopupVariables(){o3_centerpopup=ol_centerpopup;o3_centeroffset=ol_centeroffset;}
function parseCenterPopupExtras(pf,i,ar){var k=i,v;
if(k<ar.length){if(ar[k]==CENTERPOPUP){eval(pf+'centerpopup=('+pf+'centerpopup==0)?1:0');return k;}
if(ar[k]==CENTEROFFSET){k=opt_MULTIPLEARGS(++k,ar,(pf+'centeroffset'));return k;}}
return-1;}
function centerPopupHorizontal(browserWidth,horizontalScrollAmount,widthFix){if(!o3_centerpopup)return void(0);
var vdisp=o3_centeroffset.split(','),placeX,iwidth=browserWidth,winoffset=horizontalScrollAmount,pWd=parseInt(o3_width);
placeX=winoffset+Math.round((iwidth-widthFix-pWd)/2)+parseInt(vdisp[0]);if(typeof o3_followscroll!='undefined'&&o3_followscroll&&o3_sticky)o3_relx=placeX;
return placeX;}
function centerPopupVertical(browserHeight,verticalScrollAmount){if(!o3_centerpopup)return void(0);
var placeY,iheight=browserHeight,scrolloffset=verticalScrollAmount,vdisp=o3_centeroffset.split(','),pHeight=(o3_aboveheight?parseInt(o3_aboveheight):(olNs4?over.clip.height:over.offsetHeight));
placeY=scrolloffset+Math.round((iheight-pHeight)/2)+(vdisp.length>1?parseInt(vdisp[1]):0);if(typeof o3_followscroll!='undefined'&&o3_followscroll&&o3_sticky)o3_rely=placeY;
return placeY;}
registerRunTimeFunction(setCenterPopupVariables);registerCmdLineFunction(parseCenterPopupExtras);registerHook('horizontalPlacement',centerPopupHorizontal,FCHAIN);registerHook('verticalPlacement',centerPopupVertical,FCHAIN);if(olInfo.meets(4.10))registerNoParameterCommands('centerpopup');
}
