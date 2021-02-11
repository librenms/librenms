<div style="text-align: center;">
    <form class="form-inline" id="customrange">
        <input type="hidden" id="selfaction" value="http://librenms.local/graphs/to=1612928100/id=447/type=port_bits/from=1612841700/">
        <div class="form-group">
            <label for="dtpickerfrom">@lang('From')</label>
            <input type="text" class="form-control" id="dtpickerfrom" maxlength="16" value="{{ $from }}" data-date-format="YYYY-MM-DD HH:mm">
        </div>
        <div class="form-group">
            <label for="dtpickerto">@lang('To')</label>
            <input type="text" class="form-control" id="dtpickerto" maxlength=16 value="{{ $to }}" data-date-format="YYYY-MM-DD HH:mm">
        </div>
        <input type="submit" class="btn btn-default" id="submit" value="@lang('Update')" onclick="submitCustomRange(this.form)">
    </form>
    <script type="text/javascript">
        function submitCustomRange(frmdata) {
            var tsto = moment(frmdata.dtpickerto.value).unix();
            var tsfrom = moment(frmdata.dtpickerfrom.value).unix();
            var action = document.location.href;
            action = setOrReplace(action, 'to', tsto);
            action = setOrReplace(action, 'from', tsfrom);

            frmdata.action = action;
            return true;
        }

        function setOrReplace(url, key, value) {
            var search = key + '=';
            if (url.includes(search)) {
                var regex = new RegExp(search + '[0-9a-zA-Z\-]+');
                return url.replace(regex, search + value)
            }
            var sep = url.includes('?') ? '&' : '?';

            return url + sep + search + value;
        }

        $(function () {
            var strfrom = new Date($("#dtpickerfrom").val() * 1000);
            $("#dtpickerfrom").val(strfrom.getFullYear() + "-" +
                ("0" + (strfrom.getMonth() + 1)).slice(-2) + "-" +
                ("0" + strfrom.getDate()).slice(-2) + " " +
                ("0" + strfrom.getHours()).slice(-2) + ":" +
                ("0" + strfrom.getMinutes()).slice(-2)
            );
            var strto = new Date($("#dtpickerto").val() * 1000);
            $("#dtpickerto").val(strto.getFullYear() + "-" +
                ("0" + (strto.getMonth() + 1)).slice(-2) + "-" +
                ("0" + strto.getDate()).slice(-2) + " " +
                ("0" + strto.getHours()).slice(-2) + ":" +
                ("0" + strto.getMinutes()).slice(-2)
            );
            $("#dtpickerfrom").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    previous: "fa fa-chevron-left",
                    next: "fa fa-chevron-right",
                    today: "fa fa-calendar-check-o",
                    clear: "fa fa-trash-o",
                    close: "fa fa-close"
                }
            });
            $("#dtpickerto").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    previous: "fa fa-chevron-left",
                    next: "fa fa-chevron-right",
                    today: "fa fa-calendar-check-o",
                    clear: "fa fa-trash-o",
                    close: "fa fa-close"
                }
            });
        });
    </script>
</div>
