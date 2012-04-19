//\/////
//\  overLIB Set On/Off Plugin
//\  This file requires overLIB 4.10 or later.
//\
//\  overLIB 4.10 - You may not remove or change this notice.
//\  Copyright Erik Bosrup 1998-2003. All rights reserved.
//\  Contributors are listed on the homepage.
//\  See http://www.bosrup.com/web/overlib/ for details.
//\/////
//\  THIS IS A VERY MODIFIED VERSION. DO NOT EDIT OR PUBLISH. GET THE ORIGINAL!
if(typeof olInfo=='undefined'||typeof olInfo.meets=='undefined'||!olInfo.meets(4.10))alert('overLIB 4.10 or later is required for the Set On/Off Plugin.');else{registerCommands('seton,setoff');var olSetType;
function setOnOffVariables(){olSetType=0;}
function parseOnOffExtras(pf,i,ar){var k=i,v;
if(k<ar.length){if(ar[k]==SETON||ar[k]==SETOFF){olSetType=1;k=opt_MULTICOMMANDS(++k,ar);return k;}}
return-1;}
function hasCommand(istrt,args,COMMAND){for(var i=istrt;i<args.length;i++){if(typeof args[i]=='number'&& args[i]==COMMAND)return i;}
return-1;}
function scanCommandSet(pf,args){var k=-1,j,je;
if(olSetType){
while((k=hasCommand(++k,args,SETON))<args.length&&k>-1){je=opt_MULTICOMMANDS(k+1,args);for(j=k+1;j<(k+je);j++)setNoParamCommand(1,pf,args[j]);k+=(je-1);}
k=-1;while((k=hasCommand(++k,args,SETOFF))<args.length&&k>-1){je=opt_MULTICOMMANDS(k+1,args);for(j=k+1;j<(k+je);j++)setNoParamCommand(0,pf,args[j]);k+=(je-1);}}
return true;}
var olRe;
function setNoParamCommand(whichType,pf,COMMAND){var v=pms[COMMAND-1-pmStart];
if(pmt&&!olRe)olRe=eval('/'+pmt.split(',').join('|')+'/');if(pf!='ol_'&& /capturefirst/.test(v))return;if(pf!='ol_'&& /wrap/.test(v)&& eval(pf+'wrap')&&(whichType==0)){nbspCleanup();o3_width=ol_width;}
if(olRe.test(v))eval(pf+v+'='+((whichType&&COMMAND==AUTOSTATUSCAP)?whichType++:whichType));}
function opt_MULTICOMMANDS(i,ar){var k=i;
while(k<ar.length&& typeof ar[k]=='number'&& ar[k]>pmStart){k++;if(ar[k-1]=='SETON'||ar[k-1]=='SETOFF')break;}
k-=(k<ar.length?2:1);
return k;}
registerRunTimeFunction(setOnOffVariables);registerCmdLineFunction(parseOnOffExtras);registerPostParseFunction(scanCommandSet);
}
