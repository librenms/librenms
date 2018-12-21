@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">@lang('Widget title')</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="@lang('Default Title')" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="notes-{{ $id }}" class="control-label">@lang('Notes')</label>
        <textarea name="notes" id="notes-{{ $id }}" rows="3" class="form-control">{{ $notes }}</textarea>
    </div>

    <br />
    <div class="form-group">
            The following html tags are supported: &lt;b&gt;, &lt;iframe&gt;, &lt;i&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;h1&gt;, &lt;h2&gt;, &lt;h3&gt;, &lt;h4&gt;, &lt;br&gt;, &lt;p&gt;.<br />
            If you want just text then wrap in &lt;pre&gt;&lt;/pre&gt;
    </div>
@endsection
