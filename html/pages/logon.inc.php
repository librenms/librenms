<?php
if( $config['twofactor'] && isset($twofactorform) ) {
  echo twofactor_form();
}
else {
?>
      <div class="row">
        <div class="col-md-offset-4 col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">
                <center>
                  <img src="images/librenms_logo_light.png">
                </center>
              </h3>
            </div>
            <div class="panel-body">
              <div class="container-fluid">
                <form class="form-horizontal" role="form" action="" method="post" name="logonform">
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="text" name="username" id="username" class="form-control" placeholder="Username" required autofocus />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember" id="remember" /> Remember me.
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-default btn-block" name="submit" type="submit">Login</button>
                        </div>
                    </div>
                    <?php
                    if (isset($auth_message)) {
                        $msg_box[] = array('type'=>'error','message'=>$auth_message,'title'=>'Login error');
                    }
                    ?>
                    </form>
                    <script type="text/javascript">
                    <!--
                    document.logonform.username.focus();
                    // -->
                    </script>
                    <?php
                    }
                    ?>
                    </div>
                  </div>
                  <?php
                    if (isset($config['login_message'])) {
                      echo('<div class="panel-footer"><center>'.$config['login_message'].'</center></div>');
                    }
                    ?>
                </div>
              </div>
            <div class="col-md-4"></div>
          </div>
