<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
                <strong>{{ $title }}</strong> <a href="{{ $url }}">[EDIT]</a>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        {!! Str::markdown($device->notes ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                    </div>
        </div>
        </div>
    </div>
    </div>
</div>
