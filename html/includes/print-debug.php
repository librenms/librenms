<?php

    $total_queries = count($sql_debug);
    $total_php_issues = count($php_debug);
?>

<div class="modal fade" id="sql_debug" tabindex="-1" role="dialog" aria-labelledby="sql_debug_label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">SQL Debug</h4>
      </div>
      <div class="modal-body">
      <table class="table table-condensed table-hover">
<?php

foreach ($sql_debug as $sql_error) {
    echo ("
          <tr>
              <td>
                  $sql_error
              </td>
          </tr>
    ");
}

    echo ("
          <tr>
              <td>
                  $total_queries total SQL queries run.
              </td>
          </tr>
    ");

?>
      </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="php_debug" tabindex="-1" role="dialog" aria-labelledby="php_debug_label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">SQL Debug</h4>
      </div>
      <div class="modal-body">
      <table class="table table-condensed table-hover">
<?php

foreach ($php_debug as $php_error) {
    echo ("
          <tr>
              <td>
    ");
    print_r($php_error);
    echo("
              </td>
          </tr>
    ");
}

    echo ("
          <tr>
              <td>
                  $total_php_issues total PHP issues / errors.
              </td>
          </tr>
    ");

?>
      </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<nav class="navbar navbar-default navbar-fixed-bottom navbar-debug">
    <div class="container-fluid">
        <p><strong>Debug options:</strong> <a href="#" data-toggle="modal" data-target="#sql_debug">Show SQL Debug</a> / <a href="#" data-toggle="modal" data-target="#php_debug">Show PHP Debug</a></p>
    </div>
</nav>
