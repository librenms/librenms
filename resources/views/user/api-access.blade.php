@extends('layouts.librenmsv1')

@section('title', __('API Tokens'))

@section('content')
<div class="container-fluid">
    <legend>{{ __('API Tokens') }}</legend>

    @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    @if (session('api_token_plain'))
        <div class="alert alert-warning">
            <p><strong>{{ session('api_token_message', __('Copy this token now; it will not be shown again.')) }}</strong></p>
            <div class="form-group">
                <label for="api-token-once" class="control-label">{{ __('Your API token') }}</label>
                <input type="text" class="form-control" id="api-token-once" readonly value="{{ session('api_token_plain') }}">
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="list-unstyled">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <p class="text-muted">{{ __('Tokens are shown only once when created or reset. Use the REST API with the X-Auth-Token header.') }}</p>

        <table class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Disabled') }}</th>
                    <th>{{ __('Reset token') }}</th>
                    <th>{{ __('Remove') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tokens as $api)
                    <tr id="api-token-row-{{ $api->id }}" @if($api->user && $api->user->auth_type !== $legacy_auth_type) bgcolor="lightgrey" @endif>
                        <td class="api-token-description-cell" data-token-id="{{ $api->id }}">
                            <span
                                class="api-token-description-text"
                                style="cursor:pointer"
                                tabindex="0"
                                title="{{ __('Click to edit') }}"
                                data-description="{{ e($api->description) }}"
                            >@if($api->description !== ''){{ $api->description }}@else<span class="text-muted">&dash;&dash;</span>@endif</span>
                            <input type="text" class="form-control input-sm api-token-description-input hidden" maxlength="255" value="{{ e($api->description) }}" aria-label="{{ __('Description') }}">
                        </td>
                        <td>
                            <input type="checkbox"
                                   name="token-status"
                                   data-token_id="{{ $api->id }}"
                                   data-off-text="{{ __('No') }}"
                                   data-on-text="{{ __('Yes') }}"
                                   data-on-color="danger"
                                   data-size="mini"
                                   @if ($api->disabled) checked @endif>
                        </td>
                        <td>
                            <form method="post" action="{{ route('api-access.reset', $api->id) }}" class="form-inline" onsubmit="return confirm(@json(__('This revokes the current token and issues a new one. Continue?')));">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-xs">{{ __('Reset token') }}</button>
                            </form>
                        </td>
                        <td>
                            <button type="button"
                                    class="btn btn-danger btn-xs"
                                    data-token_id="{{ $api->id }}"
                                    data-toggle="modal"
                                    data-target="#confirm-delete">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">{{ __('No API tokens yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="text-center">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-token">
                {{ __('Create API access token') }}
            </button>
        </div>

        <hr>

        <legend>{{ __('API v1 tokens') }} <span class="label label-info">{{ __('Beta') }}</span></legend>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle tw:mr-2"></i>
            <strong>{{ __('Beta') }}:</strong>
            {{ __('The v1 API is in beta. Endpoints and behaviour may change without notice.') }}
        </div>

        <div id="v1-token-plain-alert" class="alert alert-warning hidden">
            <p><strong>{{ __('Copy this v1 token now; it will not be shown again.') }}</strong></p>
            <div class="form-group">
                <label for="v1-token-once" class="control-label">{{ __('Your v1 API token') }}</label>
                <input type="text" class="form-control" id="v1-token-once" readonly value="">
            </div>
        </div>

        <p class="text-muted">
            {{ __('Use these with the v1 REST API via the') }} <code>Authorization: Bearer &lt;token&gt;</code> {{ __('header.') }}
        </p>

        <table class="table table-bordered table-condensed" id="v1-tokens-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Expires') }}</th>
                    <th>{{ __('Last used') }}</th>
                    <th>{{ __('Renew') }}</th>
                    <th>{{ __('Remove') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($v1_tokens as $v1)
                    <tr id="v1-token-row-{{ $v1->id }}">
                        <td>{{ $v1->name }}</td>
                        <td>{{ $v1->created_at?->diffForHumans() ?? '—' }}</td>
                        <td class="v1-token-expires">{{ $v1->expires_at?->diffForHumans() ?? __('Never') }}</td>
                        <td>{{ $v1->last_used_at?->diffForHumans() ?? __('Never') }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-xs"
                                    data-token_id="{{ $v1->id }}"
                                    data-toggle="modal"
                                    data-target="#v1-renew-token">{{ __('Renew') }}</button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-xs"
                                    data-token_id="{{ $v1->id }}"
                                    data-toggle="modal"
                                    data-target="#v1-confirm-delete">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr id="v1-tokens-empty"><td colspan="6">{{ __('No v1 API tokens yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="text-center">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#v1-create-token">
                {{ __('Create v1 API token') }}
            </button>
        </div>
</div>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal-delete-title" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="modal-delete-title">{{ __('Confirm delete') }}</h5>
            </div>
            <div class="modal-body">
                <p>{{ __('If you would like to remove the API token then please click Delete.') }}</p>
            </div>
            <div class="modal-footer">
                <form method="post" id="remove_token_form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger" id="token-removal">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="create-token" tabindex="-1" role="dialog" aria-labelledby="modal-create-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('api-access.store') }}" class="form-horizontal">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="modal-create-title">{{ __('Create new API access token') }}</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="description" class="col-sm-3 control-label">{{ __('Description') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Create API token') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="v1-create-token" tabindex="-1" role="dialog" aria-labelledby="v1-create-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="v1-create-token-form" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="v1-create-title">{{ __('Create v1 API token') }} <span class="label label-info">{{ __('Beta') }}</span></h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="v1-token-name" class="col-sm-3 control-label">{{ __('Name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="v1-token-name" name="token_name" required maxlength="255">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="v1-token-expires" class="col-sm-3 control-label">{{ __('Expires in (days)') }}</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="v1-token-expires" name="expires_in" min="1" placeholder="{{ __('Leave blank for never') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="v1-renew-token" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="v1-renew-token-form" class="form-horizontal">
                <input type="hidden" name="v1_token_id" value="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title">{{ __('Renew v1 API token') }} <span class="label label-info">{{ __('Beta') }}</span></h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="v1-token-extend" class="col-sm-4 control-label">{{ __('Extend by (days)') }}</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="v1-token-extend" name="extend_days" min="0" value="30" required>
                            <p class="help-block">{{ __('Enter 0 for no expiration.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('Renew') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="v1-confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="v1-remove-token-form">
                <input type="hidden" name="v1_token_id" value="">
                <input type="hidden" name="confirm" value="yes">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title">{{ __('Confirm delete') }} <span class="label label-info">{{ __('Beta') }}</span></h5>
                </div>
                <div class="modal-body">
                    <p>{{ __('Delete this v1 API token? This cannot be undone.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
  (function () {
    var baseUrl = "{{ url('api-access') }}";

    function descriptionDisplayHtml(raw) {
      if (raw && String(raw).length) {
        return $('<div>').text(raw).html();
      }
      return '<span class="text-muted">&dash;&dash;</span>';
    }

    function setDescriptionView($cell, raw) {
      var $span = $cell.find('.api-token-description-text');
      $span.data('description', raw);
      $span.html(descriptionDisplayHtml(raw));
      $cell.find('.api-token-description-input').val(raw);
    }

    $("[name='token-status']").bootstrapSwitch('offColor','success');
    $('input[name="token-status"]').on('switchChange.bootstrapSwitch', function(event, state) {
      event.preventDefault();
      var token_id = $(this).data("token_id");
      $.ajax({
        type: 'PATCH',
        url: baseUrl + '/' + token_id,
        contentType: 'application/json',
        data: JSON.stringify({ disabled: state }),
        dataType: 'json'
      });
    });

    $(document).on('keydown', '.api-token-description-text', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).trigger('click');
      }
    });

    $(document).on('click', '.api-token-description-text', function (e) {
      e.preventDefault();
      var $cell = $(this).closest('.api-token-description-cell');
      if ($cell.data('editing')) {
        return;
      }
      $cell.data('editing', true);
      var $span = $(this);
      var $input = $cell.find('.api-token-description-input');
      $span.addClass('hidden');
      $input.removeClass('hidden').val($span.data('description') || '').focus().select();
    });

    $(document).on('keydown', '.api-token-description-input', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        $(this).blur();
      } else if (e.key === 'Escape') {
        e.preventDefault();
        var $cell = $(this).closest('.api-token-description-cell');
        var $span = $cell.find('.api-token-description-text');
        $(this).val($span.data('description') || '').addClass('hidden');
        $span.removeClass('hidden');
        $cell.data('editing', false);
      }
    });

    $(document).on('blur', '.api-token-description-input', function () {
      var $input = $(this);
      var $cell = $input.closest('.api-token-description-cell');
      if (!$cell.data('editing')) {
        return;
      }
      var tokenId = $cell.data('token-id');
      var $span = $cell.find('.api-token-description-text');
      var previous = $span.data('description') || '';
      var next = $input.val();
      if (next === previous) {
        $input.addClass('hidden');
        $span.removeClass('hidden');
        $cell.data('editing', false);
        return;
      }
      $.ajax({
        type: 'PATCH',
        url: baseUrl + '/' + tokenId,
        contentType: 'application/json',
        data: JSON.stringify({ description: next }),
        dataType: 'json'
      }).done(function (data) {
        if (data.description !== undefined) {
          setDescriptionView($cell, data.description);
        }
      }).fail(function () {
        $input.val(previous);
        if (typeof toastr !== 'undefined') {
          toastr.error(@json(__('Could not update description.')));
        }
      }).always(function () {
        $input.addClass('hidden');
        $span.removeClass('hidden');
        $cell.data('editing', false);
      });
    });

    $('#confirm-delete').on('show.bs.modal', function(event) {
      var token_id = $(event.relatedTarget).data('token_id');
      $('#remove_token_form').attr('action', baseUrl + '/' + token_id);
    });

    function v1FailMessage(xhr, fallback) {
      var j = xhr.responseJSON;
      if (j && j.errors) {
        return Object.values(j.errors)[0][0];
      }
      if (j && j.message) {
        return j.message;
      }
      return fallback;
    }

    function v1ToastError(msg) {
      if (typeof toastr !== 'undefined') {
        toastr.error(msg);
      } else {
        alert(msg);
      }
    }

    function v1EscapeHtml(s) {
      return $('<div>').text(s == null ? '' : String(s)).html();
    }

    $('#v1-create-token-form').on('submit', function (e) {
      e.preventDefault();
      var $form = $(this);
      $.ajax({
        type: 'POST',
        url: baseUrl + '/v1',
        contentType: 'application/json',
        data: JSON.stringify({
          token_name: $form.find('[name=token_name]').val(),
          expires_in: $form.find('[name=expires_in]').val()
        }),
        dataType: 'json'
      }).done(function (data) {
        $('#v1-tokens-empty').remove();
        $('#v1-tokens-table tbody').append(
          '<tr id="v1-token-row-' + data.token_id + '">' +
            '<td>' + v1EscapeHtml(data.token_name) + '</td>' +
            '<td>' + v1EscapeHtml(data.created_at) + '</td>' +
            '<td class="v1-token-expires">' + v1EscapeHtml(data.expires_at) + '</td>' +
            '<td>' + @json(__('Never')) + '</td>' +
            '<td><button type="button" class="btn btn-warning btn-xs" data-token_id="' + data.token_id + '" data-toggle="modal" data-target="#v1-renew-token">' + @json(__('Renew')) + '</button></td>' +
            '<td><button type="button" class="btn btn-danger btn-xs" data-token_id="' + data.token_id + '" data-toggle="modal" data-target="#v1-confirm-delete">' + @json(__('Delete')) + '</button></td>' +
          '</tr>'
        );

        $('#v1-token-once').val(data.token);
        $('#v1-token-plain-alert').removeClass('hidden');
        $('#v1-create-token').modal('hide');
        $form[0].reset();
      }).fail(function (xhr) {
        v1ToastError(v1FailMessage(xhr, @json(__('Could not create token.'))));
      });
    });

    $('#v1-renew-token').on('show.bs.modal', function (event) {
      $(this).find('[name=v1_token_id]').val($(event.relatedTarget).data('token_id'));
    });

    $('#v1-renew-token-form').on('submit', function (e) {
      e.preventDefault();
      var $form = $(this);
      var tokenId = $form.find('[name=v1_token_id]').val();
      $.ajax({
        type: 'PATCH',
        url: baseUrl + '/v1/' + tokenId + '/renew',
        contentType: 'application/json',
        data: JSON.stringify({
          extend_days: $form.find('[name=extend_days]').val()
        }),
        dataType: 'json'
      }).done(function (data) {
        $('#v1-token-row-' + tokenId).find('.v1-token-expires').text(data.expires_at);
        $('#v1-renew-token').modal('hide');
      }).fail(function (xhr) {
        v1ToastError(v1FailMessage(xhr, @json(__('Could not renew token.'))));
      });
    });

    $('#v1-confirm-delete').on('show.bs.modal', function (event) {
      $(this).find('[name=v1_token_id]').val($(event.relatedTarget).data('token_id'));
    });

    $('#v1-remove-token-form').on('submit', function (e) {
      e.preventDefault();
      var $form = $(this);
      var tokenId = $form.find('[name=v1_token_id]').val();
      $.ajax({
        type: 'DELETE',
        url: baseUrl + '/v1/' + tokenId,
        dataType: 'json'
      }).done(function () {
        $('#v1-token-row-' + tokenId).remove();
        if ($('#v1-tokens-table tbody tr').length === 0) {
          $('#v1-tokens-table tbody').append('<tr id="v1-tokens-empty"><td colspan="6">' + @json(__('No v1 API tokens yet.')) + '</td></tr>');
        }
        $('#v1-confirm-delete').modal('hide');
      }).fail(function (xhr) {
        v1ToastError(v1FailMessage(xhr, @json(__('Could not delete token.'))));
      });
    });
  })();
</script>
@endsection
