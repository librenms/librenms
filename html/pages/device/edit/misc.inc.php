<?php

echo '
<form class="form-horizontal">
    <div class="form-group">
        <label for="oxidized" class="col-sm-2 control-label">Exclude from Oxidized?</label>
        <div class="col-sm-10">
            '.dynamic_override_config('checkbox','override_Oxidized_disable', $device).'
        </div>
    </div>
</form>
';

