      <form class="form-horizontal" role="form" action="" method="post" name="logonform">
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <h3>Please log in:</h3>
          </div>
        </div>
        <div class="form-group">
          <label for="username" class="col-sm-2 control-label">Username</label>
          <div class="col-sm-6">
            <input type="text" name="username" id="username" class="form-control" placeholder="Username" />
          </div>
        </div>
        <div class="form-group">
          <label for="password" class="col-sm-2 control-label">Password</label>
          <div class="col-sm-6">
            <input type="password" name="password" id="password" class="form-control" />
          </div>
        </div>
        <div class="form-group">
          <label for="remember" class="col-sm-2 control-label">Remember Me</label>
          <div class="col-sm-6">
            <input type="checkbox" name="remember" id="remember" />
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-6">
            <button type="submit" class="btn btn-default input-sm" name="submit" type="submit">Login</button>
          </div>
        </div>
<?php
if (isset($auth_message))
{
  echo('
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-6">
            <div class="alert alert-danger text-center">' . $auth_message . '</div>
          </div>
        </div>
');
}
?>
<?php
if (isset($config['login_message']))
{
  echo('
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-6">
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

    </div>
</div>
