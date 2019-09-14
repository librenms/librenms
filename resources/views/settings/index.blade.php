@extends('layouts.librenmsv1')

@section('title', __('Settings'))

@section('content')
    <div class="container">
        <div id="app">
            <librenms-settings
                prefix="{{ url('settings') }}"
                initial-tab="{{ $active_tab }}"
                initial-section="{{ $active_section }}"
            ></librenms-settings>
        </div>
    </div>
@endsection


@push('scripts')
    @routes
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        $(".toolTip").click(function () {
            $(this).tooltip('toggle'); // toggle on tap for mobile
        }).tooltip();

        // tab and section update address bar
        $('.settings-group-tabs a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            var group = $(e.target).data('group');
            window.history.pushState(group, '', '/settings/' + group);
        });

        $('.settings-sections .collapse').on('show.bs.collapse', function (e) {
            var $target = $(e.target);
            var slug = $target.data('group') + '/' + $target.data('section');
            window.history.pushState(slug, '', '/settings/' + slug);
        }).on('hide.bs.collapse', function (e) {
            var group = $(e.target).data('group');
            window.history.pushState(group, '', '/settings/' + group);
        });

        $('#email_backend').change(function () {
            var type = this.value;
            if (type === 'sendmail') {
                $('.smtp-form').hide();
                $('.sendmail-form').show();
            } else if (type === 'smtp') {
                $('.sendmail-form').hide();
                $('.smtp-form').show();
            } else {
                $('.smtp-form').hide();
                $('.sendmail-form').hide();
            }
        }).change(); // trigger initially

        $('#geoloc\\.engine').change(function () {
            var engine = this.value;
            if (engine === 'openstreetmap') {
                $('.geoloc_api_key').hide();
            } else {
                $('.geoloc_api_key').show();
            }
        }).change(); // trigger initially

        // Checkbox config ajax calls
        $('.section-form input[type=checkbox]').on('switchChange.bootstrapSwitch', function (event, state) {
            event.preventDefault();
            var $this = $(this);
            globalConfigUpdateValue($this, state);
        }).bootstrapSwitch('offColor', 'danger');

        // Input field config ajax calls
        $('.section-form input:not([type=checkbox])').on('blur keyup', function (event) {
            if (event.type === 'keyup' && event.keyCode !== 13) {
                return;
            }
            event.preventDefault();
            var $this = $(this);
            var value = $this.val();

            globalConfigUpdateValue($this, value);
        });

        $('.section-form select').change(function (event) {
            event.preventDefault();
            var $this = $(this);
            var value = $this.val();
            globalConfigUpdateValue($this, value);
        });

        $('.config-undo').click(function (event) {
            event.preventDefault();
            var target = $(this).data('target');
            var $target = $('#' + target.replace('.', '\\.'));
            var value = $target.data('previous');
            $target.val(value);
            globalConfigUpdateValue($target, value);
        });

        $('.config-default').click(function (event) {
            event.preventDefault();
            var target = $(this).data('target');
            var $target = $('#' + target.replace('.', '\\.'));
            var value = $target.data('default');
            $target.val(value);
            globalConfigUpdateValue($target, value);
        });

        function globalConfigUpdateValue(target, value) {
            var name = target.attr('name');
            var current = target.data('current');
            if (current != value && target[0].checkValidity()) {
                $.ajax({
                    type: 'PUT',
                    url: '{{ route('settings.update', ['']) }}/' + name,
                    data: {value: value},
                    target: target,
                    dataType: "json",
                    success: function (data) {
                        globalConfigUpdateSuccess(this.target, data);
                    },
                    error: function (data) {
                        globalConfigUpdateFailure(this.target, data.responseJSON);
                    }
                });
            }
        }

        function globalConfigUpdateSuccess(target, data) {
            var current = target.val();

            target.data('previous', target.data('current'));
            target.data('current', current);
            target.closest('.form-group').addClass('has-success');
            target.next().addClass('fa-check');
            target.parent().parent().find('.config-undo').show();
            target.parent().parent().find('.config-default').toggle(current != target.data('default'));
            setTimeout(function () {
                target.closest('.form-group').removeClass('has-success');
                target.next().removeClass('fa-check');
            }, 2000);
            var message = '@lang('Config :name updated')';
            toastr.success(data.message || message.replace(':name', target.attr('name')));
        }

        function globalConfigUpdateFailure(target, data) {
            if (target.is(':checkbox')) {
                console.log(target.data('current'));
                target.bootstrapSwitch('state', target.data('current'));
            } else {
                target.val(target.data('current')).change();
            }
            target.closest('.form-group').addClass('has-error');
            target.next().addClass('fa-times');
            setTimeout(function () {
                target.closest('.form-group').removeClass('has-error');
                target.next().removeClass('fa-times');
            }, 2000);
            var message = '@lang('Error occurred updating :name')';
            toastr.error(data.message || message.replace(':name', target.attr('name')));
        }
    </script>
@endpush

