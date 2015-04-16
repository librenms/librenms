/*global Gridster:false*/
/*global chai:false, describe:false, beforeEach:false, afterEach:false, it:false*/

'use strict';

// add #fixture div when running tests with karma
if (document.body) {
    var fixture = document.createElement('div'); fixture.setAttribute('id', 'fixture');
    document.querySelector('#fixture') || document.body.appendChild(fixture);
}

var expect = chai.expect;
var serialization = {
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

var SPEED = 100;

var u = {
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

        serialize || (serialize = serialization.default());

        var widgets = [];
        $.each(serialize, function(i, w) {
            widgets.push(['<li>' + w.name + '</li>', w.size_x, w.size_y, w.col, w.row]);
        });

        this.$fixture = $('#fixture');
        this.$fixture.html('<div class="gridster"><ul></ul></div>');
        this.$el = $(".gridster > ul");

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
        var event = document.createEvent("MouseEvents");
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




describe('gridster.js', function() {
    it('should expose Gridster, Draggable, Coords and Collision classes to window', function() {
        expect(window.Gridster).to.be.a.function;
        expect(window.GridsterDraggable).to.be.a.function;
        expect(window.GridsterCoords).to.be.a.function;
        expect(window.GridsterCollision).to.be.a.function;
    });

    it('should define the jquery bridge', function() {
        expect($.fn.gridster).to.be.a('function');
    });

    describe('Initialization', function() {
        beforeEach(function() {
            u.createGridster.call(this, {
                serialize_params: function($w, wgd) {
                    return {
                        name: $w.text(), col: wgd.col, row: wgd.row,
                        size_x: wgd.size_x, size_y: wgd.size_y
                    };
                }
            });
        });

        afterEach(function() {
            this.gridster.destroy(true);
        });

        it('should access the gridster instance', function() {
            expect(this.$el.data('gridster')).to.be.an('object');
            expect(this.$el.data('gridster')).to.have.property('$widgets');
            expect(this.$el.data('gridster').$widgets).to.have.length.above(1);
        });

        it('should bind resize event to window', function() {
            var events = $._data(window, "events");
            expect(events).to.have.property('resize');
            // both gridster and dragabble resize event handlers
            expect(events.resize).to.have.length(2);
        });

        it('should respect the serialized positions', function(done) {
            var serialize = this.gridster.serialize();
            expect(serialize).to.deep.equal(serialization.default());
            done();
        });
    });


    describe('Instance methods', function() {
        describe('Register widgets from DOM', function() {
            beforeEach(function() {
                u.createGridster.call(this, {
                    serialize_params: function($w, wgd) {
                        return {
                            name: $w.text(), col: wgd.col, row: wgd.row,
                            size_x: wgd.size_x, size_y: wgd.size_y
                        };
                    }
                }, null, true);
            });

            afterEach(function() {
                this.gridster.destroy(true);
            });

            it('should sync DOM widgets with gridster widgets', function() {
                expect(this.gridster.$widgets).to.have.length(serialization.default().length);
            });

            it('should respect the serialized positions', function() {
                var serialize = this.gridster.serialize();
                expect(serialize).to.deep.equal(serialization.default());
            });
        });


        describe('Destroy', function() {

            beforeEach(function() {
                u.createGridster.call(this);
            });

            afterEach(function() {
                this.gridster.destroy(true);
            });

            it('should clean data attached but keep the grid on DOM', function() {
                this.gridster.destroy();
                expect(this.$el.data('gridster')).to.be.undefined;
                expect(document.contains(this.$el[0])).to.be.true;
            });

            it('should clean widgets data even if gridster is not removed from DOM', function() {
                var $el = this.gridster.$widgets.eq(1);
                this.gridster.destroy();
                expect($el.data('coords')).to.be.undefined;
            });

            it('should remove the grid from the DOM', function() {
                this.gridster.destroy(true);
                expect(document.contains(this.$el[0])).to.be.false;
            });

            it('should clean widgets data', function() {
                var $el = this.gridster.$widgets.eq(1);
                this.gridster.destroy(true);
                expect($el.data('coords')).to.be.undefined;
            });

            it('should unbind resize event from window', function() {
                this.gridster.destroy(true);
                expect($._data(window, "events")).to.be.undefined;
            });

            it('should clean gridmap array', function() {
                this.gridster.destroy();
                expect(this.gridster.gridmap).to.be.empty;
                expect(this.gridster.faux_grid).to.be.empty;
            });
        });


        describe('Add a widget dynamically', function() {
            beforeEach(function() {
                u.createGridster.call(this);
            });

            afterEach(function() {
                this.gridster.destroy(true);
            });

            it('should be added to the grid', function() {
                this.gridster.add_widget('<li class="new">new</li>', 2, 2);
                var $el = this.gridster.$el.find('.new');
                expect($el).to.have.length(1);
            });

            it('should be positioned in the top/leftmost available space', function() {
                this.gridster.remove_widget('[data-col=1][data-row=1]');

                this.gridster.add_widget('<li class="new">new</li>', 1, 1);
                var $el = this.gridster.$el.find('.new');

                expect($el.attr('data-col')).to.equal('1');
                expect($el.attr('data-row')).to.equal('1');
                expect(this.gridster.gridmap[1][1][0]).to.equal($el[0]);
            });

            it('should respect the specified dimensions and coords', function() {
                this.gridster.add_widget('<li class="new">new</li>', 2, 2, 2, 1);
                var $el = this.gridster.$el.find('.new');
                expect($el.attr('data-col')).to.equal('2');
                expect($el.attr('data-row')).to.equal('1');
                expect($el.attr('data-sizex')).to.equal('2');
                expect($el.attr('data-sizey')).to.equal('2');

                expect(this.gridster.gridmap[2][1][0]).to.equal($el[0]);
            });
        });


        describe('Remove a widget', function() {
            beforeEach(function() {
                u.createGridster.call(this);
            });

            afterEach(function() {
                this.gridster.destroy(true);
            });

            it('should be removed from the grid', function(done) {
                var $el = this.gridster.$el.find('[data-col=1][data-row=1]');
                this.gridster.remove_widget($el, false, function() {
                    expect(this.gridmap[1][1]).to.be.false;
                    expect(document.contains($el[0])).to.be.false;
                    done();
                });
            });

            it('should cause elements below moving up', function(done) {
                var $el1 = this.gridster.$el.find('[data-col=1][data-row=1]');
                var $el2 = this.gridster.$el.find('[data-col=2][data-row=1]');
                var $el3 = this.gridster.$el.find('[data-col=1][data-row=3]');
                var silent = false;
                this.gridster.remove_widget($el1, silent, function() {
                    this.remove_widget($el2, silent, function() {
                        expect($el3.attr('data-col')).to.equal('1');
                        expect($el3.attr('data-row')).to.equal('1');
                        done();
                    });
                });
            });

            it('shouldn\'t cause elements below moving up (silent=true)', function(done) {
                var $el1 = this.gridster.$el.find('[data-col=1][data-row=1]');
                var $el2 = this.gridster.$el.find('[data-col=2][data-row=1]');
                var $el3 = this.gridster.$el.find('[data-col=1][data-row=3]');
                var silent = true;
                this.gridster.remove_widget($el1, silent, function() {
                    this.remove_widget($el2, silent, function() {
                        expect($el3.attr('data-col')).to.equal('1');
                        expect($el3.attr('data-row')).to.equal('3');
                        done();
                    });
                });
            });
        });


        describe('Resize a widget', function() {
            beforeEach(function() {
                u.createGridster.call(this);
            });

            afterEach(function() {
                this.gridster.destroy(true);
            });

            it('should be resized to the new dimensions', function(done) {
                var $el = this.gridster.$el.find('[data-col=1][data-row=1]');
                this.gridster.resize_widget($el, 3, 3, function() {
                    expect(this.gridmap[2][2][0]).to.equal($el[0]);
                    done();
                });
            });

            it('should cause elements below to be displaced', function(done) {
                var $el = this.gridster.$el.find('[data-col=1][data-row=1]');
                var $el2 = this.gridster.$el.find('[data-col=2][data-row=1]');
                this.gridster.resize_widget($el, 3, 3, function() {
                    expect(this.gridmap[2][4][0]).to.equal($el2[0]);
                    done();
                });
            });

            it('should respect the resizing limits if specified', function(done) {
                var $el = this.gridster.$el.find('[data-col=1][data-row=1]');
                $el.attr({
                    'data-max-sizex': 2,
                    'data-max-sizey': 2
                });
                this.gridster.resize_widget($el, 3, 3, function() {
                    expect(this.gridmap[3][3][0]).to.equal($el[0]);
                    done();
                });
            });
        });
    });


    describe('Class methods', function() {
        describe('Gridster.sort_by_row_asc', function() {
            it('should sort coords by row in ascending order', function() {
                var sorted = Gridster.sort_by_row_asc(serialization.default());
                var result = u.pick(sorted, 'name').join(',');
                var expected = 'A,C,D,B,F,G,E,H,I,J,K';

                expect(result).to.equal(expected);
            });
        });

        describe('Gridster.sort_by_row_and_col_asc', function() {
            it('should sort coords by row and col (top-left) in ascending order', function() {
                var sorted = Gridster.sort_by_row_and_col_asc(serialization.default());
                var result = u.pick(sorted, 'name').join(',');
                var expected = 'A,B,C,D,F,G,E,H,I,J,K';

                expect(result).to.equal(expected);
            });
        });

        describe('Gridster.sort_by_col_asc', function() {
            it('should sort coords by col in ascending order', function() {
                var sorted = Gridster.sort_by_col_asc(serialization.default());
                var result = u.pick(sorted, 'name').join(',');
                var expected = 'A,E,K,B,C,H,D,G,I,J,F';

                expect(result).to.equal(expected);
            });
        });

        describe('Gridster.sort_by_row_desc', function() {
            it('should sort coords by row in descending order', function() {
                var sorted = Gridster.sort_by_row_desc(serialization.default());
                var result = u.pick(sorted, 'name').join(',');
                // note that size_y are taken into account
                var expected = 'K,J,H,E,I,C,B,G,A,F,D';

                expect(result).to.equal(expected);
            });
        });
    });

    describe('Interactions', function() {
        describe('Drag and drop', function() {
            beforeEach(function() {
                u.createGridster.call(this);
            });

            afterEach(function() {
                this.gridster.destroy(true);
            });

            describe('move up', function() {
                it('should displace the dragged element one cell above', function(done) {
                    var $el = this.gridster.gridmap[1][3];
                    this.drag_from_to([1, 3], [1, 1]).done(function() {
                        expect(this.gridster.gridmap[1][1][0]).to.equal($el[0]);
                        done();
                    });
                });

                it('should displace elements above under the dragged element', function(done) {
                    var $above1 = this.gridster.gridmap[1][1];
                    var $above2 = this.gridster.gridmap[2][1];

                    this.drag_from_to([1, 3], [1, 1]).done(function() {
                        expect(this.gridster.gridmap[1][2][0]).to.equal($above1[0]);
                        expect(this.gridster.gridmap[2][2][0]).to.equal($above2[0]);
                        done();
                    });
                });
            });

            describe('move down', function() {
                it('should displace the dragged element one cell below', function(done) {
                    var $el = this.gridster.gridmap[5][1];
                    this.drag_from_to([5, 1], [5, 6]).done(function() {
                        expect(this.gridster.gridmap[5][4]).to.be.ok;
                        expect(this.gridster.gridmap[5][4][0]).to.equal($el[0]);
                        done();
                    });
                });

                it('should displace elements below on the top of the dragged element', function(done) {
                    var $below1 = this.gridster.gridmap[5][3];

                    this.drag_from_to([5, 1], [5, 6]).done(function() {
                        expect(this.gridster.gridmap[5][1][0]).to.equal($below1[0]);
                        done();
                    });
                });
            });

            describe('move left', function() {
                it('should displace the dragged element one cell left', function(done) {
                    var $el = this.gridster.gridmap[2][1];
                    this.drag_from_to([2, 1], [1, 1]).done(function() {
                        expect(this.gridster.gridmap[1][1]).to.be.ok;
                        expect(this.gridster.gridmap[1][1][0]).to.equal($el[0]);
                        done();
                    });
                });

                it('should displace the item on the left under the dragged element', function(done) {
                    var $left = this.gridster.gridmap[1][1];
                    var $left_bottom = this.gridster.gridmap[1][3];
                    this.drag_from_to([2, 1], [1, 1]).done(function() {
                        expect(this.gridster.gridmap[1][3]).to.be.ok;
                        expect(this.gridster.gridmap[1][3][0]).to.equal($left[0]);
                        expect(this.gridster.gridmap[1][5]).to.be.ok;
                        expect(this.gridster.gridmap[1][5][0]).to.equal($left_bottom[0]);
                        done();
                    });
                });
            });

            describe('move right', function() {
                it('should displace the dragged element one cell right', function(done) {
                    var $el = this.gridster.gridmap[4][3];
                    this.drag_from_to([4, 3], [5, 3]).done(function() {
                        expect(this.gridster.gridmap[5][3]).to.be.ok;
                        expect(this.gridster.gridmap[5][3][0]).to.equal($el[0]);
                        done();
                    });
                });

                it('should displace the item below one cell up', function(done) {
                    var $below = this.gridster.gridmap[1][4];
                    this.drag_from_to([4, 3], [5, 3]).done(function() {
                        expect(this.gridster.gridmap[1][3]).to.be.ok;
                        expect(this.gridster.gridmap[1][3][0]).to.equal($below[0]);
                        done();
                    });
                });

                it('should displace the item on the right one cell down', function(done) {
                    var $right = this.gridster.gridmap[5][3];
                    this.drag_from_to([4, 3], [5, 3]).done(function() {
                        expect(this.gridster.gridmap[5][4]).to.be.ok;
                        expect(this.gridster.gridmap[5][4][0]).to.equal($right[0]);
                        done();
                    });
                });
            });
        });


        describe('Resize', function() {
            describe('axis', function() {
                beforeEach(function() {
                    u.createGridster.call(this, {
                        resize: {
                            enabled: true,
                            axes: ['x', 'y', 'both']
                        }
                    });
                });

                afterEach(function() {
                    this.gridster.destroy(true);
                });

                it('should resize in both x/y axis', function(done) {
                    var $el = this.gridster.gridmap[1][1];
                    var $handle = $el.find('.gs-resize-handle-both');
                    this.drag_from_to($handle, [7, 4]).done(function() {
                        expect($el.attr('data-sizex')).to.equal('7');
                        expect($el.attr('data-sizey')).to.equal('4');
                        this.drag_from_to($handle, [1, 1]).done(function() {
                            expect($el.attr('data-sizex')).to.equal('1');
                            expect($el.attr('data-sizey')).to.equal('1');
                            done();
                        });
                    });
                });

                it('should resize horizontally', function(done) {
                    var $el = this.gridster.gridmap[1][1];
                    var $handle = $el.find('.gs-resize-handle-x');
                    this.drag_from_to($handle, [6, 1]).done(function() {
                        expect($el.attr('data-sizex')).to.equal('6');
                        expect($el.attr('data-sizey')).to.equal('2');
                        this.drag_from_to($handle, [1, 1]).done(function() {
                            expect($el.attr('data-sizex')).to.equal('1');
                            expect($el.attr('data-sizey')).to.equal('2');
                            done();
                        });
                    });
                });

                it('should resize vertically', function(done) {
                    var $el = this.gridster.gridmap[1][1];
                    var $handle = $el.find('.gs-resize-handle-y');
                    this.drag_from_to($handle, [1, 6]).done(function() {
                        expect($el.attr('data-sizex')).to.equal('1');
                        expect($el.attr('data-sizey')).to.equal('6');
                        this.drag_from_to($handle, [1, 1]).done(function() {
                            done();
                        });
                    });
                });
            });

            describe('max/min size', function() {
                beforeEach(function() {
                    u.createGridster.call(this, {
                        resize: {
                            enabled: true,
                            max_size: [4, 4],
                            min_size: [2, 2]
                        }
                    });
                });

                afterEach(function() {
                    this.gridster.destroy(true);
                });

                it('should respect specified resize.max_size option', function(done) {
                    var $el = this.gridster.gridmap[1][1];
                    var $handle = $el.find('.gs-resize-handle-both');
                    this.drag_from_to($handle, [8, 8]).done(function() {
                        expect($el.attr('data-sizex')).to.equal('4');
                        expect($el.attr('data-sizey')).to.equal('4');
                        done();
                    });
                });

                it('should respect specified resize.min_size option', function(done) {
                    var $el = this.gridster.gridmap[1][1];
                    var $handle = $el.find('.gs-resize-handle-both');
                    this.drag_from_to($handle, [8, 8]).done(function() {
                        this.drag_from_to($handle, [1, 1]).done(function() {
                            expect($el.attr('data-sizex')).to.equal('2');
                            expect($el.attr('data-sizey')).to.equal('2');
                            done();
                        });
                    });
                });
            });
        });
    });

});