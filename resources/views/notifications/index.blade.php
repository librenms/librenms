@extends('layouts.librenmsv1')

@section('title', $isArchive ? __('Archive') . ' - ' . __('Notifications') : __('Notifications'))

@section('content')
<div class="container" x-data="{
    showCreate: false,
    submitting: false,
    newTitle: '',
    newBody: '',
    unreadCount: {{ $unreadCount }},
    csrf: '{{ csrf_token() }}',
    storeUrl: '{{ route('notifications.store') }}',
    readAllUrl: '{{ route('notifications.read-all') }}',
    readUrlTemplate: '{{ route('notifications.read', ['notification' => ':id']) }}',
    stickUrlTemplate: '{{ route('notifications.stick', ['notification' => ':id']) }}',
    unstickUrlTemplate: '{{ route('notifications.unstick', ['notification' => ':id']) }}',

    async submitCreate() {
        if (! this.newTitle.trim() || ! this.newBody.trim()) {
            toastr.error('{{ __('Title and message are required.') }}');
            return;
        }
        this.submitting = true;
        try {
            const res = await fetch(this.storeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title: this.newTitle,
                    body: this.newBody,
                }),
            });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                window.location.href = '{{ route('notifications.index') }}';
            } else {
                this.submitting = false;
                toastr.error(data.message || '{{ __('Failed to create notification') }}');
            }
        } catch (e) {
            this.submitting = false;
            toastr.error('{{ __('An error occurred') }}');
        }
    },

    async markRead(id, event) {
        const btn = event.currentTarget;
        btn.disabled = true;
        try {
            const res = await fetch(this.readUrlTemplate.replace(':id', id), {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                const el = document.getElementById('notif-' + id);
                if (el) {
                    el.classList.add('tw:transition-opacity', 'tw:duration-300', 'tw:opacity-0');
                    setTimeout(() => el.remove(), 300);
                }
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                $('.count-notif').text(this.unreadCount);
                if (this.unreadCount === 0) {
                    $('.count-notif.badge-danger').removeClass('badge-danger');
                }
            } else {
                btn.disabled = false;
                toastr.error(data.message || '{{ __('Failed to mark as read') }}');
            }
        } catch (e) {
            btn.disabled = false;
            toastr.error('{{ __('An error occurred') }}');
        }
    },

    async markAllRead(event) {
        const btn = event.currentTarget;
        btn.disabled = true;
        try {
            const res = await fetch(this.readAllUrl, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                window.location.reload();
            } else {
                btn.disabled = false;
                toastr.error(data.message || '{{ __('Failed to mark all as read') }}');
            }
        } catch (e) {
            btn.disabled = false;
            toastr.error('{{ __('An error occurred') }}');
        }
    },

    async stick(id, event) {
        const btn = event.currentTarget;
        btn.disabled = true;
        try {
            const res = await fetch(this.stickUrlTemplate.replace(':id', id), {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                window.location.href = '{{ route('notifications.index') }}';
            } else {
                btn.disabled = false;
                toastr.error(data.message || '{{ __('Failed to set sticky') }}');
            }
        } catch (e) {
            btn.disabled = false;
            toastr.error('{{ __('An error occurred') }}');
        }
    },

    async unstick(id, event) {
        const btn = event.currentTarget;
        btn.disabled = true;
        try {
            const res = await fetch(this.unstickUrlTemplate.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                window.location.href = '{{ route('notifications.index') }}';
            } else {
                btn.disabled = false;
                toastr.error(data.message || '{{ __('Failed to remove sticky') }}');
            }
        } catch (e) {
            btn.disabled = false;
            toastr.error('{{ __('An error occurred') }}');
        }
    }
}">
    <div class="row">
        <div class="col-md-12">
            <h1><a href="{{ route('notifications.index') }}">{{ __('Notifications') }}</a></h1>
            <h4>
                @if ($isArchive)
                    {{ __('Archive') }}
                    <a href="{{ route('notifications.index') }}" class="btn btn-default pull-right" style="margin-top:-10px;">{{ __('Show Active') }}</a>
                @else
                    <strong class="count-notif" x-text="unreadCount">{{ $unreadCount }}</strong> {{ __('Unread Notifications') }}

                    @can('create', \App\Models\Notification::class)
                        <button type="button" class="btn btn-success pull-right fa fa-plus" @click="showCreate = !showCreate" title="{{ __('Create new notification') }}" style="margin-top:-10px;"></button>
                    @endcan

                    <button type="button" x-show="unreadCount > 0" @click="markAllRead($event)" class="btn btn-success pull-right fa fa-eye tw:mr-2" title="{{ __('Mark all as Read') }}" style="margin-top:-10px;"></button>
                @endif
            </h4>
            <hr/>
        </div>
    </div>

    @if (! $isArchive)
        @can('create', \App\Models\Notification::class)
            <div x-show="showCreate" x-cloak x-transition class="tw:mb-6">
                <div class="well">
                    <form class="form-horizontal" @submit.prevent="submitCreate">
                        <div class="form-group">
                            <label for="notif_title" class="col-sm-2 control-label">{{ __('Title') }}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="notif_title" x-model="newTitle" placeholder="{{ __('Title') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notif_body" class="col-sm-2 control-label">{{ __('Message') }}</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="notif_body" x-model="newBody" placeholder="{{ __('Message') }}" required></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-success" :disabled="submitting">
                                    <span x-show="!submitting">{{ __('Add Notification') }}</span>
                                    <span x-show="submitting" x-cloak>{{ __('Saving...') }}</span>
                                </button>
                                <button type="button" class="btn btn-default tw:ml-2" @click="showCreate = false">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    @endif

    @if (! $isArchive && $sticky->isNotEmpty())
        @foreach ($sticky as $notif)
            <div class="well" id="notif-{{ $notif->notifications_id }}">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="{{ $notif->severity == 2 ? 'text-danger' : 'text-warning' }}">
                            <strong><i class="fa fa-bell-o"></i>&nbsp;{{ $notif->title }}</strong>
                            <span class="pull-right">
                                @if ($notif->user_id != Auth::id())
                                    <code>Sticky by {{ $notif->sticky_username ?? 'Unknown' }}</code>
                                @else
                                    <button type="button" class="btn btn-primary fa fa-bell-slash-o" @click="unstick({{ $notif->notifications_id }}, $event)" title="{{ __('Remove Sticky') }}" style="margin-top:-10px;"></button>
                                @endif
                            </span>
                        </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <blockquote{!! $notif->severity == 2 ? ' style="border-color: darkred;"' : '' !!}>
                            <p>{!! \LibreNMS\Util\Clean::html($notif->body, ['HTML.Allowed' => 'br']) !!}</p>
                            <footer>{{ $notif->datetime }} | Source: <code>{{ $notif->source }}</code></footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        @endforeach
        <hr/>
    @endif

    @if ($notifications->isNotEmpty())
        @foreach ($notifications as $notif)
            @php
                $severityClass = match ((int) $notif->severity) {
                    2 => 'text-danger',
                    1 => 'text-warning',
                    default => 'text-success',
                };
            @endphp
            <div class="well" id="notif-{{ $notif->notifications_id }}">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="{{ $severityClass }}">
                            {{ $notif->title }}
                            <span class="pull-right">
                                @can('update', $notif)
                                    <button type="button" class="btn btn-primary fa fa-bell-o" @click="stick({{ $notif->notifications_id }}, $event)" title="{{ __('Mark as Sticky') }}" style="margin-top:-10px;"></button>
                                @endcan
                                @if (! $isArchive)
                                    <button type="button" class="btn btn-primary fa fa-eye" @click="markRead({{ $notif->notifications_id }}, $event)" title="{{ __('Mark as Read') }}" style="margin-top:-10px;"></button>
                                @endif
                            </span>
                        </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <blockquote{!! $notif->severity == 2 ? ' style="border-color: darkred;"' : '' !!}>
                            <p>{!! \LibreNMS\Util\Clean::html($notif->body, ['HTML.Allowed' => 'br']) !!}</p>
                            <footer>{{ $notif->datetime }} | Source: <code>{{ $notif->source }}</code></footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        @endforeach
    @elseif (! $isArchive && $sticky->isEmpty())
        <div class="alert alert-info">{{ __('No notifications.') }}</div>
    @elseif ($isArchive)
        <div class="alert alert-info">{{ __('No archived notifications.') }}</div>
    @endif

    @if (! $isArchive)
        <div class="row">
            <div class="col-md-12">
                <h3><a class="btn btn-default" href="{{ route('notifications.archive') }}">{{ __('Show Archive') }}</a></h3>
            </div>
        </div>
    @endif
</div>
@endsection
