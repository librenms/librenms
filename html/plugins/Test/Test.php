<?php

class Test {
  public function menu() {
    echo('<li><a href="plugin/p='.get_class().'">'.get_class().'</a></li>');
  }
}

?>
