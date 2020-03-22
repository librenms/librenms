@extends('device.index')

@section('tab')
    <div class="panel panel-default">
        <div class="panel-heading">
            <form method="post" role="form" id="map" class="form-inline">
                <?php echo csrf_field() ?>
                <div class="form-group">
                    <label for="dtpickerfrom">From</label>
                    <input type="text" class="form-control" id="dtpickerfrom" name="dtpickerfrom" maxlength="16"
                           value="<?php echo $vars['dtpickerfrom']; ?>" data-date-format="YYYY-MM-DD HH:mm">
                </div>
                <div class="form-group">
                    <label for="dtpickerto">To</label>
                    <input type="text" class="form-control" id="dtpickerto" name="dtpickerto" maxlength=16
                           value="<?php echo $vars['dtpickerto']; ?>" data-date-format="YYYY-MM-DD HH:mm">
                </div>
                <input type="submit" class="btn btn-default" id="submit" value="Update">
            </form>
        </div>
        <br>
        <div style="margin:0 auto;width:99%;">
        </div>
    </div>
@endsection