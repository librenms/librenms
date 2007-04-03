//\/////
//\  overLIB Follow Scroll Plugin
//\  This file requires overLIB 4.10 or later.
//\
//\  overLIB 4.10 - You may not remove or change this notice.
//\  Copyright Erik Bosrup 1998-2004. All rights reserved.
//\  Contributors are listed on the homepage.
//\  See http://www.bosrup.com/web/overlib/ for details.
//\/////
//\  THIS IS A VERY MODIFIED VERSION. DO NOT EDIT OR PUBLISH. GET THE ORIGINAL!
if(typeof olInfo=='undefined'||typeof olInfo.meets=='undefined'||!olInfo.meets(4.10))alert('overLIB 4.10 or later is required for the Follow Scroll Plugin.');else{registerCommands('followscroll,followscrollrefresh');
if(typeof ol_followscroll=='undefined')var ol_followscroll=0;if(typeof ol_followscrollrefresh=='undefined')var ol_followscrollrefresh=100;
var o3_followscroll=0,o3_followscrollrefresh=100;
function setScrollVariables(){o3_followscroll=ol_followscroll;o3_followscrollrefresh=ol_followscrollrefresh;}
function parseScrollExtras(pf,i,ar){var k=i,v;if(k<ar.length){if(ar[k]==FOLLOWSCROLL){eval(pf+'followscroll=('+pf+'followscroll==0)?1:0');return k;}
if(ar[k]==FOLLOWSCROLLREFRESH){eval(pf+'followscrollrefresh='+ar[++k]);return k;}}
return-1;}
function scroll_placeLayer(){var placeX,placeY,widthFix=0;
if(o3_frame.innerWidth){widthFix=Math.ceil(1.2*(o3_frame.outerWidth-o3_frame.innerWidth));widthFix=(widthFix>50)?20:widthFix;iwidth=o3_frame.innerWidth;}else if(eval('o3_frame.'+docRoot)&&eval("typeof o3_frame."+docRoot+".clientWidth=='number'")&&eval('o3_frame.'+docRoot+'.clientWidth'))
iwidth=eval('o3_frame.'+docRoot+'.clientWidth');
winoffset=(olIe4)?eval('o3_frame.'+docRoot+'.scrollLeft'):o3_frame.pageXOffset;
placeX=runHook('horizontalPlacement',FCHAIN,iwidth,winoffset,widthFix);
if(o3_frame.innerHeight)iheight=o3_frame.innerHeight;else if(eval('o3_frame.'+docRoot)&&eval("typeof o3_frame."+docRoot+".clientHeight=='number'")&&eval('o3_frame.'+docRoot+'.clientHeight'))
iheight=eval('o3_frame.'+docRoot+'.clientHeight');
scrolloffset=(olIe4)?eval('o3_frame.'+docRoot+'.scrollTop'):o3_frame.pageYOffset;
placeY=runHook('verticalPlacement',FCHAIN,iheight,scrolloffset);
repositionTo(over,placeX,placeY);
if(o3_followscroll&&o3_sticky&&(o3_relx||o3_rely)&&(typeof o3_draggable=='undefined'||!o3_draggable)){if(typeof over.scroller=='undefined'||over.scroller.canScroll)over.scroller=new Scroller(placeX-winoffset,placeY-scrolloffset,o3_followscrollrefresh);}}
function Scroller(X,Y,refresh){this.canScroll=0;this.refresh=refresh;this.x=X;this.y=Y;this.timer=setTimeout("repositionOver()",this.refresh);}
function cancelScroll(){if(!o3_followscroll||typeof over.scroller=='undefined')return;over.scroller.canScroll=1;
if(over.scroller.timer){clearTimeout(over.scroller.timer);over.scroller.timer=null;}}
function getPageScrollY(){if(o3_frame.pageYOffset)return o3_frame.pageYOffset;if(eval(docRoot))return eval('o3_frame.'+docRoot+'.scrollTop');return-1;}
function getPageScrollX(){if(o3_frame.pageXOffset)return o3_frame.pageXOffset;if(eval(docRoot))return eval('o3_frame.'+docRoot+'.scrollLeft');return-1;}
function getLayerTop(layer){if(layer.pageY)return layer.pageY;if(layer.style.top)return parseInt(layer.style.top);return-1;}
function getLayerLeft(layer){if(layer.pageX)return layer.pageX;if(layer.style.left)return parseInt(layer.style.left);return-1;}
function repositionOver(){var X,Y,pgLeft,pgTop;pgTop=getPageScrollY();pgLeft=getPageScrollX();X=getLayerLeft(over)-pgLeft;Y=getLayerTop(over)-pgTop;
if(X!=over.scroller.x||Y!=over.scroller.y)repositionTo(over,pgLeft+over.scroller.x,pgTop+over.scroller.y);over.scroller.timer=setTimeout("repositionOver()",over.scroller.refresh);}
registerRunTimeFunction(setScrollVariables);registerCmdLineFunction(parseScrollExtras);registerHook("hideObject",cancelScroll,FAFTER);registerHook("placeLayer",scroll_placeLayer,FREPLACE);if(olInfo.meets(4.10))registerNoParameterCommands('followscroll');}
