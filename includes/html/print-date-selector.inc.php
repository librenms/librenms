<div style="text-align: center;">
    <form class="form-inline" id="customrange">
        <input type="hidden" id="selfaction" value="<?php echo Request::url(); ?>">
        <div class="form-group">
            <label for="dtpickerfrom">From</label>
            <input type="text"
                   class="form-control"
                   id="dtpickerfrom"
                   maxlength="16"
                   value="<?php echo $graph_array['from']; ?>"
                   data-date-format="YYYY-MM-DD HH:mm">
        </div>
        <div class="form-group">
            <label for="dtpickerto">To</label>
            <input type="text"
                   class="form-control"
                   id="dtpickerto"
                   maxlength=16
                   value="<?php echo $graph_array['to']; ?>"
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
            var strfrom = new Date($("#dtpickerfrom").val()*1000);
            $("#dtpickerfrom").val(strfrom.getFullYear()+"-"+
                ("0"+(strfrom.getMonth()+1)).slice(-2)+"-"+
                ("0"+strfrom.getDate()).slice(-2)+" "+
                ("0"+strfrom.getHours()).slice(-2)+":"+
                ("0"+strfrom.getMinutes()).slice(-2)
            );
            var strto = new Date($("#dtpickerto").val()*1000);
            $("#dtpickerto").val(strto.getFullYear()+"-"+
                ("0"+(strto.getMonth()+1)).slice(-2)+"-"+
                ("0"+strto.getDate()).slice(-2)+" "+
                ("0"+strto.getHours()).slice(-2)+":"+
                ("0"+strto.getMinutes()).slice(-2)
            );
            $("#dtpickerfrom").datetimepicker({useCurrent: true, sideBySide: true, useStrict: false, icons: {time: "fa fa-clock-o", date: "fa fa-calendar", up: "fa fa-chevron-up", down: "fa fa-chevron-down", previous: "fa fa-chevron-left", next: "fa fa-chevron-right", today: "fa fa-calendar-check-o", clear: "fa fa-trash-o", close: "fa fa-close"}});
            $("#dtpickerto").datetimepicker({useCurrent: true, sideBySide: true, useStrict: false, icons: {time: "fa fa-clock-o", date: "fa fa-calendar", up: "fa fa-chevron-up", down: "fa fa-chevron-down", previous: "fa fa-chevron-left", next: "fa fa-chevron-right", today: "fa fa-calendar-check-o", clear: "fa fa-trash-o", close: "fa fa-close"}});
        });
    </script>
</div>
