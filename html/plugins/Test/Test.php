<?php

class Test {
  public function menu() {
    echo('<li><a href="plugin/p='.get_class().'">'.get_class().'</a></li>');
  }

  public function device_overview_container($device) {
    echo('<div class="container-fluid"><div class="row"> <div class="col-md-12"> <div class="panel panel-default panel-condensed"> <div class="panel-heading"><strong>Test Plugin</strong> </div> i"ve just added a plugin :) <br>');
    echo('<pre>');
    var_dump($device);
    echo('</pre>');
    echo('</div></div></div></div>');
  }
}

?>
