//\/////
//\  overLIB Crossframe Support Plugin
//\  This file requires overLIB 4.10 or later.
//\
//\  overLIB 4.05 - You may not remove or change this notice.
//\  Copyright Erik Bosrup 1998-2004. All rights reserved.
//\  Contributors are listed on the homepage.
//\  See http://www.bosrup.com/web/overlib/ for details.
//\/////
//\  THIS IS A VERY MODIFIED VERSION. DO NOT EDIT OR PUBLISH. GET THE ORIGINAL!
if(typeof olInfo=='undefined'||typeof olInfo.meets=='undefined'||!olInfo.meets(4.10))alert('overLIB 4.10 or later is required for the Cross Frame Support Plugin.');else{registerCommands('frame');
function parseFrameExtras(pf,i,ar){var k=i,v;
if(k<ar.length){if(ar[k]==FRAME){v=ar[++k];if(pf=='ol_')ol_frame=v;else opt_FRAME(v);return k;}}
return-1;}
function opt_FRAME(frm){o3_frame=frm;over=createDivContainer('overDiv');return 0;}
function frmDepth(thisFrame,ofrm){var retVal='';
for(var i=0;i<thisFrame.length;i++){if(thisFrame[i].length>0){retVal=frmDepth(thisFrame[i],ofrm);if(retVal=='')continue;}else if(thisFrame[i]!=ofrm)continue;retVal='['+i+']'+retVal;break;}
return retVal;}
function getFrmRef(srcFrm,tgetFrm){var rtnVal=''
if(tgetFrm!=srcFrm){var tFrm=frmDepth(top.frames,tgetFrm)
var sFrm=frmDepth(top.frames,srcFrm)
if(sFrm.length==tFrm.length){l=tFrm.lastIndexOf('[')
if(l){while( sFrm.substring(0,l)!=tFrm.substring(0,l) )
l=tFrm.lastIndexOf('[',l-1)
tFrm=tFrm.substr(l)
sFrm=sFrm.substr(l)}}
var cnt=0,p='',str=tFrm
while((k=str.lastIndexOf('['))!=-1){cnt++
str=str.substring(0,k)}
for(var i=0;i<cnt;i++)p=p+'parent.'
rtnVal=p+'frames'+sFrm+'.'}
return rtnVal}
function chkForFrmRef(){if(o3_frame!=ol_frame)fnRef=getFrmRef(ol_frame,o3_frame)
return true;}
registerCmdLineFunction(parseFrameExtras);registerPostParseFunction(chkForFrmRef);
}
