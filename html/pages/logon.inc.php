<?php
if( $config['twofactor'] && isset($twofactorform) ) {
  echo twofactor_form();
} else { 
?>
      <form class="form-horizontal" role="form" action="" method="post" name="logonform">
        <div class="form-group">
          <div class="col-sm-offset-4 col-sm-4">
            <h3>Please log in:</h3>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-4 col-sm-4">
            <input type="text" name="username" id="username" class="form-control" placeholder="Username" required autofocus />
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-4 col-sm-4">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" />
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-4 col-sm-4">
            <div class="checkbox">
                <input type="checkbox" name="remember" id="remember" />
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-4 col-sm-4">
            <button type="submit" class="btn btn-default btn-block" name="submit" type="submit">Login</button>
          </div>
        </div>
<?php
if (isset($auth_message))
{
    $msg_box[] = array('type'=>'error','message'=>$auth_message,'title'=>'Login error');
}
?>
<?php
if (isset($config['login_message']))
{
  echo('
        <div class="form-group">
          <div class="col-sm-offset-4 col-sm-4">
            <div class="alert alert-info text-center">'.$config['login_message'].'</div>
          </div>
        </div>');
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
