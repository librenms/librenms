@extends('layouts.librenmsv1')

@section('title', __('API Tokens'))

@section('content')
<div class="container-fluid" x-data="apiAccessManager()">
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

    <p class="text-muted">{{ __('Tokens are shown only once when created or reset. Use with the REST API via the X-Auth-Token or Authorization: Bearer <token> header.') }}</p>

    <table class="table table-bordered table-condensed">
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Created') }}</th>
                <th>{{ __('Last used') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Reset token') }}</th>
                <th>{{ __('Remove') }}</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="token in tokens" :key="token.id">
                <tr>
                    <td>
                        <div x-show="editingTokenId !== token.id">
                            <span
                                style="cursor: pointer;"
                                tabindex="0"
                                title="{{ __('Click to edit') }}"
                                @click="startEditDescription(token, $event)"
                                @keydown.enter.prevent="startEditDescription(token, $event)"
                                @keydown.space.prevent="startEditDescription(token, $event)"
                            >
                                <span x-text="token.name || '--'" :class="{ 'text-muted': !token.name }"></span>
                            </span>
                        </div>
                        <div x-show="editingTokenId === token.id" x-cloak>
                            <input
                                type="text"
                                class="form-control input-sm"
                                maxlength="255"
                                x-model="editingText"
                                @keydown.enter.prevent="saveDescription(token)"
                                @keydown.escape.prevent="cancelEditDescription()"
                                @blur="saveDescription(token)"
                                aria-label="{{ __('Description') }}"
                            >
                        </div>
                    </td>
                    <td x-text="token.created_human"></td>
                    <td x-text="token.last_used_human"></td>
                    <td>
                        <button type="button"
                                class=""
                                @click="openExpirationModal(token)"
                                title="{{ __('Click to change expiration') }}">
                            <span class="label" :class="'label-' + token.status_label" x-text="token.expires_human"></span>
                            <i class="fa fa-pencil text-muted tw:ml-1"></i>
                        </button>
                    </td>
                    <td>
                        <form method="post" :action="baseUrl + '/' + token.id + '/reset'" class="form-inline" onsubmit="return confirm(@js(__('This revokes the current token and issues a new one. Continue?')));">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-xs">{{ __('Reset token') }}</button>
                        </form>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-danger btn-xs"
                                @click="openDeleteModal(token)">{{ __('Delete') }}</button>
                    </td>
                </tr>
            </template>
            <tr x-show="tokens.length === 0">
                <td colspan="6">{{ __('No API tokens yet.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="text-center">
        <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal()">
            {{ __('Create API access token') }}
        </button>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal show="deleteModalOpen" title="{{ __('Confirm delete') }}" maxWidth="sm">
        <p>{{ __('If you would like to remove the API token then please click Delete.') }}</p>
        <x-slot:footer>
            <button type="button" class="btn btn-default" @click="deleteModalOpen = false">{{ __('Cancel') }}</button>
            <form method="post" :action="baseUrl + '/' + deleteTokenId" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
            </form>
        </x-slot:footer>
    </x-modal>

    {{-- Create Token Modal --}}
    <x-modal show="createModalOpen" title="{{ __('Create new API access token') }}" maxWidth="lg">
        <form id="create-api-token-form" method="post" action="{{ route('api-access.store') }}" class="form-horizontal">
            @csrf
            <div class="form-group">
                <label for="description" class="col-sm-3 control-label">{{ __('Description') }}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}" placeholder="{{ __('Description') }}">
                </div>
            </div>
            <div class="form-group">
                <label for="expires_in" class="col-sm-3 control-label">{{ __('Expires in') }}</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input type="number" class="form-control" id="expires_in" name="expires_in" x-model="createExpiresIn" min="1" placeholder="{{ __('Leave blank for never') }}">
                        <span class="input-group-addon">{{ __('days') }}</span>
                    </div>
                    <div class="btn-group btn-group-xs tw:mt-2" role="group">
                        <button type="button" class="btn btn-default" @click="createExpiresIn = ''">{{ __('Never') }}</button>
                        <button type="button" class="btn btn-default" @click="createExpiresIn = 7">7 {{ __('days') }}</button>
                        <button type="button" class="btn btn-default" @click="createExpiresIn = 30">30 {{ __('days') }}</button>
                        <button type="button" class="btn btn-default" @click="createExpiresIn = 90">90 {{ __('days') }}</button>
                        <button type="button" class="btn btn-default" @click="createExpiresIn = 365">1 {{ __('year') }}</button>
                    </div>
                </div>
            </div>
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-default" @click="createModalOpen = false">{{ __('Cancel') }}</button>
            <button type="submit" form="create-api-token-form" class="btn btn-success">{{ __('Create API token') }}</button>
        </x-slot:footer>
    </x-modal>

    {{-- Set Expiration Modal --}}
    <x-modal show="expModalOpen" title="{{ __('Set Token Expiration') }}" maxWidth="lg">
        <form id="set-expiration-form" @submit.prevent="saveExpiration" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ __('Token') }}</label>
                <div class="col-sm-9">
                    <p class="form-control-static" x-text="expTokenName"></p>
                </div>
            </div>
            <div class="form-group">
                <label for="modal_expires_in" class="col-sm-3 control-label">{{ __('Expires in') }}</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input type="number" class="form-control" id="modal_expires_in" x-model="expExpiresIn" :disabled="expDisabled" min="1" placeholder="{{ __('Leave blank for never') }}">
                        <span class="input-group-addon">{{ __('days') }}</span>
                    </div>
                    <div class="btn-group btn-group-xs tw:mt-2" role="group">
                        <button type="button" class="btn btn-default" @click="setPresetDays('')">{{ __('Never') }}</button>
                        <button type="button" class="btn btn-default" @click="setPresetDays(7)">7 {{ __('days') }}</button>
                        <button type="button" class="btn btn-default" @click="setPresetDays(30)">30 {{ __('days') }}</button>
                        <button type="button" class="btn btn-default" @click="setPresetDays(90)">90 {{ __('days') }}</button>
                        <button type="button" class="btn btn-default" @click="setPresetDays(365)">1 {{ __('year') }}</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="modal_disabled" x-model="expDisabled" @change="onDisabledToggle">
                            <span class="text-danger"><strong>{{ __('Disable token (revoke access immediately)') }}</strong></span>
                        </label>
                    </div>
                </div>
            </div>
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-default" @click="expModalOpen = false">{{ __('Cancel') }}</button>
            <button type="submit" form="set-expiration-form" class="btn btn-primary" :disabled="expSaving">
                <span x-show="!expSaving">{{ __('Save') }}</span>
                <span x-show="expSaving" x-cloak><i class="fa fa-spinner fa-spin"></i> {{ __('Saving...') }}</span>
            </button>
        </x-slot:footer>
    </x-modal>
</div>
@endsection

@section('javascript')
<script>
function apiAccessManager() {
  return {
    tokens: @js($tokens->map(function ($token) {
        $isExpired = ! is_null($token->expires_at) && $token->expires_at->isPast();

        return [
            'id' => $token->id,
            'name' => (string) $token->name,
            'created_human' => $token->created_at?->diffForHumans() ?? '—',
            'last_used_human' => $token->last_used_at?->diffForHumans() ?? __('Never'),
            'expires_at' => $token->expires_at?->toIso8601String(),
            'expires_human' => $isExpired ? __('Disabled') : ($token->expires_at ? __('Expires :time', ['time' => $token->expires_at->diffForHumans()]) : __('Active')),
            'status_label' => $isExpired ? 'danger' : ($token->expires_at ? 'info' : 'success'),
            'disabled' => $isExpired,
        ];
    })),
    baseUrl: @js(url('api-access')),
    csrfToken: @js(csrf_token()),

    editingTokenId: null,
    editingText: '',

    expModalOpen: false,
    expTokenId: null,
    expTokenName: '',
    expExpiresIn: '',
    expDisabled: false,
    expSaving: false,

    deleteModalOpen: false,
    deleteTokenId: null,

    createModalOpen: false,
    createExpiresIn: @js(old('expires_in', '')),

    startEditDescription(token, event) {
      this.editingTokenId = token.id;
      this.editingText = token.name;
      this.$nextTick(() => {
        const container = event ? event.target.closest('td') : null;
        const input = container ? container.querySelector('input') : null;
        if (input) {
          input.focus();
          input.select();
        }
      });
    },

    cancelEditDescription() {
      this.editingTokenId = null;
      this.editingText = '';
    },

    async saveDescription(token) {
      if (this.editingTokenId !== token.id) {
        return;
      }
      const newName = this.editingText;
      const previousName = token.name;
      this.editingTokenId = null;

      if (newName === previousName) {
        return;
      }

      try {
        const response = await fetch(`${this.baseUrl}/${token.id}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
          },
          body: JSON.stringify({ description: newName }),
        });

        if (!response.ok) {
          throw new Error();
        }

        const data = await response.json();
        token.name = data.name ?? data.description ?? newName;
        if (typeof toastr !== 'undefined') {
          toastr.success(@js(__('Description updated.')));
        }
      } catch (err) {
        token.name = previousName;
        if (typeof toastr !== 'undefined') {
          toastr.error(@js(__('Could not update description.')));
        }
      }
    },

    openExpirationModal(token) {
      this.expTokenId = token.id;
      this.expTokenName = token.name || @js(__('api-token'));
      this.expDisabled = token.disabled;
      this.expExpiresIn = '';
      this.expSaving = false;
      this.expModalOpen = true;
    },

    setPresetDays(days) {
      this.expExpiresIn = days;
      this.expDisabled = false;
    },

    onDisabledToggle() {
      if (this.expDisabled) {
        this.expExpiresIn = '';
      }
    },

    async saveExpiration() {
      if (!this.expTokenId || this.expSaving) {
        return;
      }
      this.expSaving = true;

      const payload = { disabled: this.expDisabled };
      if (!this.expDisabled) {
        payload.expires_in = this.expExpiresIn === '' ? 0 : parseInt(this.expExpiresIn, 10);
      }

      try {
        const response = await fetch(`${this.baseUrl}/${this.expTokenId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
          },
          body: JSON.stringify(payload),
        });

        if (!response.ok) {
          throw new Error();
        }

        const data = await response.json();
        const token = this.tokens.find(t => t.id === this.expTokenId);
        if (token) {
          token.disabled = data.disabled;
          token.expires_human = data.expires_human;
          token.status_label = data.status_label;
          token.expires_at = data.expires_at;
        }

        this.expModalOpen = false;
        if (typeof toastr !== 'undefined') {
          toastr.success(@js(__('Expiration updated.')));
        }
      } catch (err) {
        if (typeof toastr !== 'undefined') {
          toastr.error(@js(__('Could not update expiration.')));
        }
      } finally {
        this.expSaving = false;
      }
    },

    openDeleteModal(token) {
      this.deleteTokenId = token.id;
      this.deleteModalOpen = true;
    },

    openCreateModal() {
      this.createExpiresIn = @js(old('expires_in', ''));
      this.createModalOpen = true;
    },
  };
}
</script>
@endsection
