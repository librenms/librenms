<br>
<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-primary">
            <div class="panel-heading">Sync status: <strong>{{ $history['node_info']['status'] }}</strong></div>
            <ul class="list-group">
                <li class="list-group-item"><strong>Node:</strong> {{ $history['node_info']['name'] }}</li>
                <li class="list-group-item"><strong>IP:</strong> {{ $history['node_info']['ip'] }}</li>
                <li class="list-group-item"><strong>Model:</strong> {{ $history['node_info']['model'] }}</li>
                <li class="list-group-item"><strong>Last Sync:</strong> {{ $history['node_info']['last_sync'] }}</li>
                <li class="list-group-item"><strong>Source:</strong> {{ $history['node_info']['source'] }}</li>
            </ul>
        </div>
    </div>

    @if($history['config_total'] > 1)
        <div class="col-sm-8">
            <form class="form-horizontal" action="" method="post">
                @csrf

                <div class="form-group">
                    <label for="config" class="col-sm-2 control-label">Config version</label>
                    <div class="col-sm-6">
                        <select id="config" name="config" class="form-control">
                            @foreach($history['config_versions'] as $version)
                                <option value="{{ $version['oid'] }}|{{ $version['date'] }}|{{ $version['version'] }}"
                                    @if($history['current_config']['oid'] === $version['oid']) selected @endif
                                >@if($history['current_config']['oid'] === $version['oid'] && isset($history['previous_config']))+@elseif($history['current_config']['oid'] === $version['oid'])*@elseif(isset($history['previous_config']['oid']) && $history['previous_config']['oid'] === $version['oid'])&nbsp;-@else&nbsp;&nbsp;@endif{{ $version['version'] }} :: {{ $version['date'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-6">
                        <input type="hidden" name="prevconfig" value="{{ implode('|', $history['current_config']) }}">
                        <button type="submit" class="btn btn-primary btn-sm" name="show">Show version</button>
                        <button type="submit" class="btn btn-primary btn-sm" name="diff">Show diff</button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
