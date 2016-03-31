<?php 

if (!$os){
  if (strstr($sysDescr, 'VSP-')){
    $os = 'avaya-vsp';
  }
}
