<div class="tab-content" style="margin-top: 15px;">
    <div role="tabpanel" class="tab-pane active" id="main">
        <legend>{{ __('Rule setup') }}</legend>
        <div class='form-group' title="The description of this alert rule.">
            <label for='rule_name' class='col-sm-3 col-md-2 control-label'>{{ __('Rule name') }}</label>
            <div class='col-sm-9 col-md-10'>
                <input type='text' id='rule_name' name='name' class='form-control validation' maxlength='200' required>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-3 col-md-2">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" id="import-from" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        {{ __('Import from') }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="import-from" id="import-dropdown">
                        <li><a href="#" name="import-query" id="import-query">SQL Query</a></li>
                        <li><a href="#" name="import-old-format" id="import-old-format">Old Format</a></li>
                        <li><a href="#" name="import-collection" id="import-collection">Collection</a></li>
                        <li><a href="#" name="import-alert_rule" id="import-alert_rule">Alert Rule</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-9 col-md-10">
                <div id="builder"></div>
            </div>
        </div>
        <div class='form-group form-inline'>
            <label for='invert' class='col-sm-3 col-md-2 control-label' title="Invert the match. If the rule matches, the alert is considered OK.">Invert match result </label>
            <div class='col-sm-2' title="Invert the match. If the rule matches, the alert is considered OK.">
                <input type="checkbox" name="invert" id="invert">
            </div>
        </div>

        <legend>{{ __('Targeting') }}</legend>

        <div class="form-group form-inline">
            <label for='maps' class='col-sm-3 col-md-2 control-label' title="Restrict this alert rule to the selected devices, groups, or locations.">Devices, groups, and locations </label>
            <div class="col-sm-7" style="width: 56%;">
                <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
            </div>
            <div>
                <label for='invert_map' class='col-md-1' style="width: 14.1333%;" text-align="left" title="If ON, alert rule checks will run on all devices except the selected devices and groups.">Run on all devices except selected </label>
                <input type='checkbox' name='invert_map' id='invert_map'>
            </div>
        </div>

        <legend>{{ __('Notifications') }}</legend>
        <div class="form-group" title="How to display the alert.  OK: green, Warning: yellow, Critical: red">
            <label for='severity' class='col-sm-3 col-md-2 control-label'>{{ __('Severity') }}</label>
            <div class="col-sm-2">
                <select name='severity' id='severity' class='form-control'>
                    <option value='ok' {{ ($default_severity ?? '') === 'ok' ? 'selected' : '' }}>OK</option>
                    <option value='warning' {{ ($default_severity ?? '') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value='critical' {{ ($default_severity ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
        </div>
        <div class="form-group form-inline">
            <label for='count' class='col-sm-3 col-md-2 control-label' title="How many notifications to issue while active before stopping. -1 means no limit. If interval is 0, this has no effect.">Max alerts </label>
            <div class="col-sm-2" title="How many notifications to issue while active before stopping. -1 means no limit. If interval is 0, this has no effect.">
                <input type='text' id='count' name='count' class='form-control' size="4" value="{{ $default_max_alerts ?? '' }}">
            </div>
            <div class="col-sm-3" title="How long to wait before issuing a notification. If the alert clears before the delay, no notification will be issued. (s,m,h,d)">
                <label for='delay' class='control-label' style="vertical-align: top;">Delay </label>
                <input type='text' id='delay' name='delay' class='form-control' size="4" value="{{ $default_delay ?? '' }}">
            </div>
            <div class="col-sm-4 col-md-3" title="How often to re-issue notifications while this alert is active. 0 means notify once. This is affected by the poller interval. (s,m,h,d)">
                <label for='interval' class='control-label' style="vertical-align: top;">Interval </label>
                <input type='text' id='interval' name='interval' class='form-control' size="4" value="{{ $default_interval ?? '' }}">
            </div>
        </div>
        <div class='form-group form-inline'>
            <label for='mute' class='col-sm-3 col-md-2 control-label' title="Show alert status in the webui, but do not issue notifications.">Mute alerts </label>
            <div class='col-sm-2' title="Show alert status in the webui, but do not issue notifications.">
                <input type="checkbox" name="mute" id="mute">
            </div>
        </div>
        <div class='form-group form-inline'>
            <label for='recovery' class='col-sm-3 col-md-2 control-label' title="Send recovery notification when alert clears.">Recovery alerts </label>
            <div class='col-sm-2' title="Send recovery notification when alert clears.">
                <input type="checkbox" name="recovery" id="recovery">
            </div>
        </div>
        <div class='form-group form-inline'>
            <label for='acknowledgement' class='col-sm-3 col-md-2 control-label' title="Send acknowledgement notification when alert is acknowledged.">Acknowledgement alerts </label>
            <div class='col-sm-2' title="Send acknowledgement notification when alert is acknowledged.">
                <input type="checkbox" name="acknowledgement" id="acknowledgement">
            </div>
        </div>

        <legend>{{ __('Delivery channels') }}</legend>
        <div class="form-group" title="Restricts this alert rule to specified transports.">
            <label for="transports" class="col-sm-3 col-md-2 control-label">Transports </label>
            <div class="col-sm-9 col-md-10">
                <select id="transports" name="transports[]" class="form-control" multiple="multiple"></select>
            </div>
        </div>

        <legend>{{ __('Templates') }}</legend>
        <div class="form-group" title="Select the template to use for notifications.">
            <label for='template_id' class='col-sm-3 col-md-2 control-label'>{{ __('Global Template') }}</label>
            <div class='col-sm-9 col-md-10'>
                <select id="template_id" name="template_id" class="form-control">
                    <option value="">{{ __('Use default (or per-transport overrides)') }}</option>
                    @foreach(($templates ?? []) as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                    @endforeach
                </select>
                <span class="help-block">{{ __('Optionally choose a global template for all transports. You can override per transport below.') }}</span>
            </div>
        </div>
        <div class="form-group" id="per-transport-templates" title="Optionally override the template for specific transports.">
            <label class='col-sm-3 col-md-2 control-label'>{{ __('Per-transport overrides') }}</label>
            <div class='col-sm-9 col-md-10'>
                <div id="transport-template-list"></div>
                <span class="help-block">{{ __('After selecting transports above, choose a template for any you want to override.') }}</span>
            </div>
        </div>

        <legend>{{ __('Notes & documentation') }}</legend>
        <div class='form-group' title="A link to some documentation on how to handle this alert. This will be included in notifications.">
            <label for='proc' class='col-sm-3 col-md-2 control-label'>Procedure URL </label>
            <div class='col-sm-9 col-md-10'>
                <input type='text' id='proc' name='proc' class='form-control validation' pattern='(http|https)://.*' maxlength='80'>
            </div>
        </div>
        <div class='form-group' title="A brief description for this alert rule">
            <label for='notes' class='col-sm-3 col-md-2 control-label'>Notes</label>
            <div class='col-sm-9 col-md-10'>
                <textarea class="form-control" rows="6" name="notes" id='notes'></textarea>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="advanced">
        <div class='form-group'>
            <label for='override_query' class='col-sm-3 col-md-2 control-label'>Override SQL</label>
            <div class='col-sm-9 col-md-10'>
                <input type='checkbox' name='override_query' id='override_query'>
            </div>
        </div>
        <div class='form-group'>
            <label for='adv_query' class='col-sm-3 col-md-2 control-label'>SQL</label>
            <div class='col-sm-9 col-md-10'>
                <textarea id='adv_query' name='adv_query' class='form-control' rows="3"></textarea>
                <span class="help-block">{{ __('Optional: Provide a raw SQL WHERE clause to override the builder.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10">
        <button id="btn-save" type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ url('alert-rules') }}" class="btn btn-default">{{ __('Cancel') }}</a>
    </div>
</div>
