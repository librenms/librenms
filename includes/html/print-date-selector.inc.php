<div style="text-align: center;">
    <form class="form-inline" id="customrange">
        <input type="hidden" id="selfaction" value="<?php echo Request::url(); ?>">
        <div class="form-group">
            <label for="dtpickerfrom">From</label>
            <input type="text"
                   class="form-control"
                   id="dtpickerfrom"
                   maxlength="16"
                   data-date-format="YYYY-MM-DD HH:mm">
        </div>
        <div class="form-group">
            <label for="dtpickerto">To</label>
            <input type="text"
                   class="form-control"
                   id="dtpickerto"
                   maxlength=16
                   data-date-format="YYYY-MM-DD HH:mm">
        </div>
        <input type="submit"
               class="btn btn-default"
               id="submit"
               value="Update"
               onclick="submitCustomRange(this.form);">
    </form>
    <script type="text/javascript">
        $(function () {
            <?php
            $ds_tz = session('preferences.timezone');
            $ds_datefrom = new DateTime();
            $ds_datefrom->setTimezone(new DateTimeZone($ds_tz));
            $ds_datefrom->setTimestamp($graph_array['from']);
            $ds_dateto = new DateTime();
            $ds_dateto->setTimezone(new DateTimeZone($ds_tz));
            $ds_dateto->setTimestamp($graph_array['to']);
            ?>
            var ds_datefrom = new Date('<?php echo $ds_datefrom->format('D M d Y H:i:s O'); ?>');
            var ds_dateto = new Date('<?php echo $ds_dateto->format('D M d Y H:i:s O'); ?>');
            var ds_tz = '<?php echo $ds_tz; ?>';

            $("#dtpickerfrom").datetimepicker({useCurrent: true, sideBySide: true, useStrict: false, icons: {time: "fa fa-clock-o", date: "fa fa-calendar", up: "fa fa-chevron-up", down: "fa fa-chevron-down", previous: "fa fa-chevron-left", next: "fa fa-chevron-right", today: "fa fa-calendar-check-o", clear: "fa fa-trash-o", close: "fa fa-close"}, defaultDate: ds_datefrom, timeZone: ds_tz});
            $("#dtpickerto").datetimepicker({useCurrent: true, sideBySide: true, useStrict: false, icons: {time: "fa fa-clock-o", date: "fa fa-calendar", up: "fa fa-chevron-up", down: "fa fa-chevron-down", previous: "fa fa-chevron-left", next: "fa fa-chevron-right", today: "fa fa-calendar-check-o", clear: "fa fa-trash-o", close: "fa fa-close"}, defaultDate: ds_dateto, timeZone: ds_tz});
        });
    </script>
</div>
