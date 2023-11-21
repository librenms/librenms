<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
                <strong>{{ $title }}</strong> <a href="{{ $url }}">[EDIT]</a>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        {{-- This is a comment.  Below we output the markdown output unescaped because we want the raw html
                         to be output to the page.  Be careful with unescaped output as it can lead to security issues. --}}
                        {!! Str::markdown($device->notes ?? '') !!}
                    </div>
        </div>
        </div>
    </div>
    </div>
</div>
