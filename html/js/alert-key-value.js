(function ($) {
    if (window.AlertKeyValue) {
        return;
    }
    function addRow(element, name, value) {
        var index = Number(element.dataset.nextIndex || 0);
        element.dataset.nextIndex = index + 1;
        var prefix = element.dataset.name + '-rows[' + index + ']';
        var row = $('<div class="row alert-key-value-row" style="margin-bottom: 5px"></div>');
        var nameInput = $('<input type="text" class="form-control" placeholder="Name">')
            .attr({name: prefix + '[name]', 'aria-label': 'Entry name'}).val(name);
        var valueInput = $('<textarea rows="1" class="form-control" placeholder="Value or template"></textarea>')
            .attr({name: prefix + '[value]', 'aria-label': 'Entry value'}).val(value);
        row.append($('<div class="col-xs-4"></div>').append(nameInput));
        row.append($('<div class="col-xs-6"></div>').append(valueInput));
        row.append($('<div class="col-xs-2"></div>').append(
            $('<button type="button" class="btn btn-default alert-key-value-remove" aria-label="Remove entry">&times;</button>')
        ));
        $(element).find('.alert-key-value-rows').append(row);
    }

    window.AlertKeyValue = {
        clear: function (element) {
            $(element).find('.alert-key-value-rows').empty();
            element.dataset.nextIndex = 0;
        },
        set: function (element, values) {
            this.clear(element);
            if (typeof values === 'string') {
                var legacy = Object.create(null);
                values.split(/\r\n|\r|\n/).forEach(function (line) {
                    var separator = line.indexOf('=');
                    if (separator !== -1) {
                        var name = line.slice(0, separator).trim();
                        if (name !== '') {
                            legacy[name] = line.slice(separator + 1).trim();
                        }
                    }
                });
                Object.entries(legacy).forEach(function (entry) {
                    addRow(element, entry[0], entry[1]);
                });
            } else {
                Object.entries(values || {}).forEach(function (entry) {
                    addRow(element, entry[0], entry[1]);
                });
            }
        }
    };

    $(document).on('click', '.alert-key-value-add', function () {
        addRow(this.closest('.alert-key-value'), '', '');
    });
    $(document).on('click', '.alert-key-value-remove', function () {
        $(this).closest('.alert-key-value-row').remove();
    });
})(jQuery);
