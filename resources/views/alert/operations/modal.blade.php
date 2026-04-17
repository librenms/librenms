<div class="modal fade" id="edit-alert-operation" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ __('Alert operation') }}</h4>
            </div>
            <div class="modal-body">
                <div id="alert-operation-form-error" class="alert alert-danger" style="display:none;"></div>
                <form id="form-alert-operation" class="form-horizontal">
                    @csrf
                    <input type="hidden" name="operation_id" id="ao_operation_id" value="">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ __('Name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name" id="ao_name" required maxlength="255">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" title="{{ __('Used when a segment step duration is 0 (repeat interval). Leave empty to use the global default from settings.') }}">{{ __('Default step duration') }}</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="default_operation_step_duration_seconds" id="ao_default_step_duration" min="0" step="1" placeholder="">
                            <p class="help-block">{{ __('Seconds. When a segment’s step duration is 0, this value is the repeat interval. Leave empty to use the global default from settings.') }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ __('Segments') }}</label>
                        <div class="col-sm-9">
                            <p class="help-block">{{ __('Each segment defines escalation steps, timing, and transports. Add multiple segments for different step ranges or channels.') }}</p>
                            <div class="table-responsive">
                                <table class="table table-condensed" id="ao-segments-table">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Steps from') }}</th>
                                        <th>{{ __('Steps to') }}</th>
                                        <th>{{ __('Start (s)') }}</th>
                                        <th>{{ __('Step duration (s)') }}</th>
                                        <th style="width:3em;"></th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <button type="button" class="btn btn-default btn-sm" id="btn-ao-add-segment">{{ __('Add segment') }}</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn-save-alert-operation">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
