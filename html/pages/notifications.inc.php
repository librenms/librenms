<?php
/* Copyright (C) 2015 Daniel Preussker, QuxLabs UG <preussker@quxlabs.com>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Notification Page
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Notifications
 */

$notifications = new ObjCache('notifications');
?>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h1><a href="/notifications">Notifications</a></h1>
      <h4><strong class="count-notif"><?php echo $notifications['count']; ?></strong> Unread Notifications <?php echo ($_SESSION['userlevel'] == 10 ? '<button class="btn btn-success pull-right new-notif" style="margin-top:-10px;">New</button>' : ''); ?></h4>
      <hr/>
    </div>
  </div>
</div>
<div class="container new-notif-collapse">
  <div class="row">
    <div class="col-md-12">
      <form class="form-horizontal new-notif-form">
        <div class="form-group">
          <label for="notif_title" class="col-sm-2 control-label">Title</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="notif_title" name="notif_title" placeholder="">
          </div>
        </div>
        <div class="form-group">
          <label for="notif_body" class="col-sm-2 control-label">Message</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="notif_body" name="notif_body"></textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-success">Add Notification</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php if (!isset($vars['archive'])) { ?>
<div class="container">
<?php
    foreach ($notifications['sticky'] as $notif) {
        if (is_numeric($notif['source'])) {
            $notif['source'] = dbFetchCell('select username from users where user_id =?',array($notif['source']));
        } ?>
  <div class="well">
    <div class="row">
      <div class="col-md-12">
        <h4 class="text-warning" id="<?php echo $notif['notifications_id']; ?>"><strong><i class="fa fa-bell-o"></i>&nbsp;&nbsp;&nbsp;<?php echo $notif['title']; ?></strong>&nbsp;<span class="pull-right"><?php echo ($notif['user_id'] != $_SESSION['user_id'] ? '<code>Sticky by '.dbFetchCell('select username from users where user_id = ?',array($notif['user_id'])).'</code>' : '<button class="btn btn-primary fa fa-bell-slash-o unstick-notif" data-toggle="tooltip" data-placement="bottom" title="Remove Sticky" style="margin-top:-10px;"></button>'); ?></span></h4>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <blockquote>
          <p><?php echo $notif['body']; ?></p>
          <footer>Source: <code><?php echo $notif['source']; ?></code></footer>
        </blockquote>
      </div>
    </div>
  </div>
<?php    } ?>
<?php    if ($notifications['sticky_count'] != 0) { ?>
<hr/>
<?php    } ?>
<?php
    foreach ($notifications['unread'] as $notif) {
        if (is_numeric($notif['source'])) {
            $notif['source'] = dbFetchCell('select username from users where user_id =?',array($notif['source']));
        } ?>
  <div class="well">
    <div class="row">
      <div class="col-md-12">
        <h4 class="text-success" id="<?php echo $notif['notifications_id']; ?>"><strong><?php echo $notif['title']; ?></strong><span class="pull-right">
<?php echo ($_SESSION['userlevel'] == 10 ? '<button class="btn btn-primary fa fa-bell-o stick-notif" data-toggle="tooltip" data-placement="bottom" title="Mark as Sticky" style="margin-top:-10px;"></button>' : ''); ?>
&nbsp;
<button class="btn btn-primary fa fa-eye read-notif" data-toggle="tooltip" data-placement="bottom" title="Mark as Read" style="margin-top:-10px;"></button>
</span>
</h4>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <blockquote>
          <p><?php echo $notif['body']; ?></p>
          <footer>Source: <code><?php echo $notif['source']; ?></code></footer>
        </blockquote>
      </div>
    </div>
  </div>
<?php    } ?>
  <div class="row">
    <div class="col-md-12">
      <h3><a class="btn btn-default" href="/notifications/archive">Show Archive</a></h3>
    </div>
  </div>
</div>
<?php } else if (isset($vars['archive'])) { ?>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h2>Archive</h2>
    </div>
  </div>
<?php    foreach (array_reverse($notifications['read']) as $notif) { ?>
  <div class="well">
    <div class="row">
      <div class="col-md-12">
        <h4 id="<?php echo $notif['notifications_id']; ?>"><?php echo $notif['title']; echo ($_SESSION['userlevel'] == 10 ? '<span class="pull-right"><button class="btn btn-primary fa fa-bell-o stick-notif" data-toggle="tooltip" data-placement="bottom" title="Mark as Sticky" style="margin-top:-10px;"></button></span>' : ''); ?>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <blockquote>
          <p><?php echo $notif['body']; ?></p>
          <footer>Source: <code><?php echo $notif['source']; ?></code></footer>
        </blockquote>
      </div>
    </div>
  </div>
<?php    } ?>
</div>
<?php } ?>
<script>
$(function() {
  $('[data-toggle="tooltip"]').tooltip();
  $('.new-notif-collapse').fadeOut(0);
  $(document).on( "click", ".new-notif", function() {
      $('.new-notif-collapse').fadeToggle();
  });

  $(document).on( "submit", ".new-notif-form", function() {
    var notif = {};
    data = $(this).serializeArray();
    for( var field in data ) {
      notif[data[field].name] = data[field].value;
    }
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: {type: 'notifications', title: notif.notif_title, body: notif.notif_body, action: 'create'},
      dataType: "json",
      success: function (data) {
        if( data.status == "ok" ) {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
          window.location.href="/notifications";
        }
        else {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
        }
      }
    });
    return false;
  });

  $(document).on( "click", ".read-notif", function() {
    $(this).attr("disabled", true);
    var notif = $(this).parent().parent().attr('id');
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: {type: 'notifications', notification_id: notif, action: 'read'},
      dataType: "json",
      success: function (data) {
        if( data.status == "ok" ) {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
          $("#"+notif).parent().parent().parent().fadeOut();
          $(".count-notif").each(function(){
            this.innerHTML = this.innerHTML-1;
          });
        }
        else {
          $(this).attr("disabled", false);
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
        }
      }
    });
  });

  $(document).on( "click", ".stick-notif", function() {
    var notif = $(this).parent().parent().attr('id');
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: {type: 'notifications', notification_id: notif, action: 'stick'},
      dataType: "json",
      success: function (data) {
        if( data.status == "ok" ) {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
          window.location.href="/notifications";
        }
        else {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
        }
      }
    });
  });

  $(document).on( "click", ".unstick-notif", function() {
    var notif = $(this).parent().parent().attr('id');
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: {type: 'notifications', notification_id: notif, action: 'unstick'},
      dataType: "json",
      success: function (data) {
        if( data.status == "ok" ) {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
          window.location.href="/notifications";
        }
        else {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
        }
      }
    });
  });

});
</script>
