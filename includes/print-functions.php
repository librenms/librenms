<?php

function print_temperature($temp_current, $temp_limit) {
  
  $temp_cur = $temp_current - 15;
  $temp_lim = $temp_limit - 15;

  if(($temp_lim - $temp_cur) > 25) { $style = "color: LimeGreen;"; }
  if(($temp_lim - $temp_cur) <= 25) { $style = "color: Green;"; }
  if(($temp_lim - $temp_cur) < 20) { $style = "color: Blue;"; }
  if(($temp_lim - $temp_cur) < 15) { $style = "color: MediumPurple;"; }
  if(($temp_lim - $temp_cur) < 10) { $style = "font-weight: bold; color: Tomato;"; }
  if(($temp_lim - $temp_cur) < 5) { $style = "font-weight: bold; color: OrangeRed;"; }
  if(($temp_lim - $temp_cur) <= 0) { $style = "font-weight: bold; color: Crimson;"; }


  return("<span style='$style'>$temp_current</span>");

}

?>
