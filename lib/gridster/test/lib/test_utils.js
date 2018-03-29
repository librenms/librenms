/* global u, SPEED*/

'use strict';

window.serialization = {
    'default': function() {
        return [
            {name: 'A', col: 1, row: 1, size_x: 1, size_y: 2},
            {name: 'B', col: 2, row: 1, size_x: 3, size_y: 2},
            {name: 'C', col: 5, row: 1, size_x: 3, size_y: 2},
            {name: 'D', col: 8, row: 1, size_x: 2, size_y: 1},
            {name: 'E', col: 1, row: 3, size_x: 4, size_y: 1},
            {name: 'F', col: 10, row: 1, size_x: 1, size_y: 2},
            {name: 'G', col: 8, row: 2, size_x: 2, size_y: 1},
            {name: 'H', col: 5, row: 3, size_x: 3, size_y: 2},
            {name: 'I', col: 8, row: 3, size_x: 1, size_y: 1},
            {name: 'J', col: 9, row: 3, size_x: 2, size_y: 2},
            {name: 'K', col: 1, row: 4, size_x: 1, size_y: 3}
        ];
    }
};


window.u = {
    pick: function(data, prop) {
        return data.map(function(elm) {
            return elm[prop];
        });
    },

    getRandomColor: function() {
        var letters = '0123456789ABCDEF'.split('');
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.round(Math.random() * 10)];
        }
        return color;
    },

    createGridster: function(config, serialize, fromDom) {
        var defaults = {
            widget_margins: [5, 5],
            widget_base_dimensions: [100, 55],
            min_cols: 10,
            max_cols: 10
        };

        serialize || (serialize = window.serialization.default());

        var widgets = [];
        $.each(serialize, function(i, w) {
            widgets.push(['<li>' + w.name + '</li>', w.size_x, w.size_y, w.col, w.row]);
        });

        this.$fixture = $('#fixture');
        this.$fixture.html('<div class="gridster"><ul></ul></div>');
        this.$el = $('.gridster > ul');

        if (fromDom) {
            var html = [];
            $.each(serialize, function(i, w) {
                html.push('<li data-col="' + w.col + '" data-row="' + w.row + '" data-sizex="' + w.size_x + '" data-sizey="' + w.size_y + '">' + w.name + '</li>');
            });
            this.$el.html(html.join('\n'));
        }

        this.gridster = this.$el.gridster(
            $.extend({}, defaults, config)).data('gridster');

        if (!fromDom) {
            $.each(widgets, function(i, widget) {
                this.gridster.add_widget.apply(this.gridster, widget);
            }.bind(this));
        }

        this.drag_from_to = u._dragFromTo;

        return this.gridster;
    },

    createEvent: function(type, offset) {
        var event = document.createEvent('MouseEvents');
        event.initMouseEvent(type, true, true, window, 0, 0, 0,
            offset.left, offset.top, false, false, false, false, 0, null);

        return event;
    },

    dispatchEvent: function(elem, type, event) {
        if (elem.dispatchEvent) {
            elem.dispatchEvent(event);
        } else if (elem.fireEvent) {
            elem.fireEvent('on' + type, event);
        }
    },

    _dragFromTo: function(fromCoords, toCoords) {
        var d = $.Deferred();
        var gridster = this.gridster;
        var $el;

        function getMousePos(coords) {
            var size = gridster.options.widget_base_dimensions;
            var margin = gridster.options.widget_margins;

            var left = ((coords[0] - 1) * size[0]) + (margin[0] * ((coords[0] * 2) - 1));
            var top = ((coords[1] - 1) * size[1]) + (margin[1] * ((coords[1] * 2) - 1));

            var parentOffset = gridster.$wrapper.offset();

            return {
                left: parentOffset.left + left + 20,
                top: parentOffset.top + top + 20
            };
        }

        function addPoint(offset) {
            $('<span/>').css({
                left: offset.left + 'px',
                top: offset.top + 'px',
                width: '2px',
                height: '2px',
                background: 'red',
                display: 'inline-block',
                position: 'absolute',
                zIndex: '9999'
            }).appendTo('body');
        }

        var from_offset;

        if (fromCoords instanceof $) {
            $el = fromCoords;
            var offset = $el.offset();
            from_offset = {
                left: offset.left + ($el.width() / 2),
                top: offset.top + ($el.height() / 2)
            };
        } else {
            $el = this.gridster.gridmap[fromCoords[0]][fromCoords[1]];
            from_offset = getMousePos(fromCoords);
        }

        if (! $el) {
            return;
        }

        var to_offset = getMousePos(toCoords);
        var el = $el.get(0);

        addPoint(from_offset);
        addPoint(to_offset);

        // Simulating drag start
        var type = 'mousedown';
        var event = u.createEvent(type, from_offset);
        u.dispatchEvent(el, type, event);

        // Simulating drop
        type = 'mousemove';
        u.dispatchEvent(el, type, u.createEvent(type, {
            top: from_offset.top + 2,
            left: from_offset.left + 2
        }));

        this.gridster.$el.on('gridster:dragstop gridster:resizestop', function() {
            setTimeout(function() {
                d.resolveWith(this);
            }.bind(this), SPEED);
        }.bind(this));

        var diff_x = to_offset.left - from_offset.left;
        var diff_y = to_offset.top - from_offset.top;
        var steps = 10;
        var step_x = diff_x / steps;
        var step_y = diff_y / steps;

        var tmp_offset = {
            left: from_offset.left,
            top: from_offset.top
        };

        for (var i = 0; i < steps; i++) {
            tmp_offset.left += step_x;
            tmp_offset.top += step_y;
            addPoint(tmp_offset);
            u.dispatchEvent(el, type, u.createEvent(type, tmp_offset));
        }

        u.dispatchEvent(el, type, u.createEvent(type, to_offset));
        addPoint(to_offset);

        // Simulating drag end
        type = 'mouseup';
        var dragEndEvent = u.createEvent(type, to_offset);
        u.dispatchEvent(el, type, dragEndEvent);

        return d.promise();
    }
};
