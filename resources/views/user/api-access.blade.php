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
  })();
</script>
@endsection
