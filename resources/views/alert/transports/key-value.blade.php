<div class="alert-key-value" id="{{ $item['name'] }}" data-name="{{ $item['name'] }}">
    <input type="hidden" name="{{ $item['name'] }}-rows" value="">
    <div class="alert-key-value-rows"></div>
    <button type="button" class="btn btn-default btn-sm alert-key-value-add">Add entry</button>
    <p class="help-block">{{ $item['descr'] }}</p>
</div>
