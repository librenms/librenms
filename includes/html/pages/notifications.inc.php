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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * Notification Page
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 */

use App\Models\User;
use LibreNMS\ObjectCache;

$notifications = new ObjectCache('notifications');
?>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h1><a href="/notifications">Notifications</a></h1>
      <h4>
<?php
echo '<strong class="count-notif">' . $notifications['count'] . '</strong> Unread Notifications ';

if (Auth::user()->hasGlobalAdmin()) {
    echo '<button class="btn btn-success pull-right fa fa-plus new-notif" data-toggle="tooltip" data-placement="bottom" title="Create new notification" style="margin-top:-10px;"></button>';
}

if ($notifications['count'] > 0 && ! isset($vars['archive'])) {
    echo '<button class="btn btn-success pull-right fa fa-eye read-all-notif" data-toggle="tooltip" data-placement="bottom" title="Mark all as Read" style="margin-top:-10px;"></button>';
}
?>
      </h4>
      <hr/>
    </div>
  </div>
</div>
<div class="container new-notif-collapse">
  <div class="row">
    <div class="col-md-12">
      <form class="form-horizontal new-notif-form">
        <?php echo csrf_field() ?>
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
<?php if (! isset($vars['archive'])) { ?>
<div class="container">
    <?php
    foreach ($notifications['sticky'] as $notif) {
        if (is_numeric($notif['source'])) {
            $notif['source'] = dbFetchCell('select username from users where user_id =?', [$notif['source']]);
        }
        echo '<div class="well"><div class="row"> <div class="col-md-12">';

        $class = $notif['severity'] == 2 ? 'text-danger' : 'text-warning';
        echo "<h4 class='$class' id='${notif['notifications_id']}'>";
        echo "<strong><i class='fa fa-bell-o'></i>&nbsp;${notif['title']}</strong>";
        echo "<span class='pull-right'>";

        if ($notif['user_id'] != Auth::id()) {
            $sticky_user = User::find($notif['user_id']);
            echo "<code>Sticky by {$sticky_user->username}</code>";
        } else {
            echo '<button class="btn btn-primary fa fa-bell-slash-o unstick-notif" data-toggle="tooltip" data-placement="bottom" title="Remove Sticky" style="margin-top:-10px;"></button>';
        }

        echo '</span></h4>'; ?>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <blockquote<?php echo $notif['severity'] == 2 ? ' style="border-color: darkred;"' : '' ?>>
          <p><?php echo \LibreNMS\Util\Clean::html($notif['body'], ['HTML.Allowed' => 'br']); ?></p>
          <footer><?php echo $notif['datetime']; ?> | Source: <code><?php echo $notif['source']; ?></code></footer>
        </blockquote>
      </div>
    </div>
  </div>
    <?php
    } ?>
    <?php    if ($notifications['sticky_count'] != 0) { ?>
<hr/>
    <?php    } ?>
    <?php
    foreach ($notifications['unread'] as $notif) {
        if (is_numeric($notif['source'])) {
            $source_user = User::find($notif['source']);
            $notif['source'] = $source_user->username;
        }
        echo '<div class="well"><div class="row"> <div class="col-md-12">';
        d_echo($notif);
        $class = 'text-success';
        if ($notif['severity'] == 1) {
            $class = 'text-warning';
        } elseif ($notif['severity'] == 2) {
            $class = 'text-danger';
        }
        echo "<h4 class='$class' id='${notif['notifications_id']}'>${notif['title']}<span class='pull-right'>";

        if (Auth::user()->hasGlobalAdmin()) {
            echo '<button class="btn btn-primary fa fa-bell-o stick-notif" data-toggle="tooltip" data-placement="bottom" title="Mark as Sticky" style="margin-top:-10px;"></button>';
        } ?>

<button class="btn btn-primary fa fa-eye read-notif" data-toggle="tooltip" data-placement="bottom" title="Mark as Read" style="margin-top:-10px;"></button>
</span>
</h4>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
          <blockquote<?php echo $notif['severity'] == 2 ? ' style="border-color: darkred;"' : '' ?>>
          <p><?php echo \LibreNMS\Util\Clean::html($notif['body'], ['HTML.Allowed' => 'br']); ?></p>
          <footer><?php echo $notif['datetime']; ?> | Source: <code><?php echo $notif['source']; ?></code></footer>
        </blockquote>
      </div>
    </div>
  </div>
    <?php
    } ?>
  <div class="row">
    <div class="col-md-12">
      <h3><a class="btn btn-default" href="notifications/archive/">Show Archive</a></h3>
    </div>
  </div>
</div>
<?php } elseif (isset($vars['archive'])) { ?>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h2>Archive</h2>
    </div>
  </div>
    <?php
    foreach ($notifications['read'] as $notif) {
        echo '<div class="well"><div class="row"> <div class="col-md-12"><h4';
        if ($notif['severity'] == 1) {
            echo ' class="text-warning"';
        } elseif ($notif['severity'] == 2) {
            echo ' class="text-danger"';
        }
        echo  " id='${notif['notifications_id']}'>${notif['title']}";

        if (Auth::user()->isAdmin()) {
            echo '<span class="pull-right"><button class="btn btn-primary fa fa-bell-o stick-notif" data-toggle="tooltip" data-placement="bottom" title="Mark as Sticky" style="margin-top:-10px;"></button></span>';
        } ?>
        </h4>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
          <blockquote<?php echo $notif['severity'] == 2 ? ' style="border-color: darkred;"' : '' ?>>
          <p><?php echo \LibreNMS\Util\Clean::html($notif['body'], ['HTML.Allowed' => 'br']); ?></p>
          <footer><?php echo $notif['datetime']; ?> | Source: <code><?php echo $notif['source']; ?></code></footer>
        </blockquote>
      </div>
    </div>
  </div>
    <?php
    } ?>
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
          window.location.href="notifications";
        } else {
          toastr.error(data.message);
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
              var new_count = this.innerHTML-1;
              this.innerHTML = new_count;
              if (new_count == 0) {
                  $this = $(this);
                  if ($this.hasClass('badge-danger')) {
                      $this.removeClass('badge-danger');
                  }
              }
          });
        }
        else {
          $(this).attr("disabled", false);
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
        }
      }
    });
  });

  $(document).on( "click", ".read-all-notif", function() {
    $(this).attr("disabled", true);
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: {type: 'notifications', action: 'read-all-notif'},
      dataType: "json",
      success: function (data) {
        if( data.status == "ok" ) {
          window.location.reload()
        }
        else {
          $(this).attr("disabled", false);
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
          window.location.href="notifications";
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
          window.location.href="notifications";
        }
        else {
          $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
        }
      }
    });
  });

});
</script>
