<form class="form-inline">
    <div class="input-group">
        <input id="settings-search" type="search" class="form-control" placeholder="@lang('Search Settings')" style="border-radius: 4px">
    </div>
</form>

@push('scripts')
    <script>
        var settings_suggestions = new Bloodhound({
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            datumTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: "ajax/bloodhound/settings?term=%QUERY",
                wildcard: "%QUERY"
            }
        });
        var settings_search = $('#settings-search').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            source: settings_suggestions.ttAdapter(),
            async: true,
            displayKey: 'description',
            valueKey: 'name',
            templates: {
                suggestion: Handlebars.compile('<p><strong>@{{name}}</strong> - <small>@{{description}}</small></p>')
            },
            limit: 20
        }).on('typeahead:select', function (ev, suggestion) {
            $('.settings-group-tabs a[href="#tab-' + suggestion.group + '"]').tab('show');
            $('#' + suggestion.group + '-' + suggestion.section ).collapse('show');
            $('#' + suggestion.name).focus();
            settings_search.typeahead('val','');
        }).on('keyup', function (e) {
            // on enter go to the first selection
            if (e.which === 13) {
                $('.tt-selectable').first().trigger( "click" );
            }
        });
    </script>
@endpush
