/*
 * jquery.draggable
 * https://github.com/ducksboard/gridster.js
 *
 * Copyright (c) 2012 ducksboard
 * Licensed under the MIT licenses.
 */

;(function(root, factory) {
    if (typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    } else if (typeof define === 'function' && define.amd) {
        define('gridster-draggable', ['jquery'], factory);
    } else {
        root.GridsterDraggable = factory(root.$ || root.jQuery);
    }

}(this, function($) {

    var defaults = {
        items: 'li',
        distance: 1,
        limit: true,
        offset_left: 0,
        autoscroll: true,
        scroll_ms: 10,
        scroll_px: 4,
        scroll_trigger_width: 40,
        scroller: null,
        ignore_dragging: ['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON'], // or function
        handle: null,
        container_width: null,  // null == auto
        move_element: true,
        helper: false,  // or 'clone'
        remove_helper: true
        // drag: function(e) {},
        // start : function(e, ui) {},
        // stop : function(e) {}
    };

    var $window = $(window);
    var isTouch = !!('ontouchstart' in window);

    var uniqId = (function() {
        var idCounter = 0;
        return function() {
            return ++idCounter + '';
        };
    })();

    var scrollParent = (function() {
        function parents(node, ps) {
            if (node.parentNode === null) { return ps; }
            return parents(node.parentNode, ps.concat([node]));
        }

        function overflow(node) {
            var style = getComputedStyle(node, null);
            return (style.getPropertyValue('overflow') +
                    style.getPropertyValue('overflow-y') +
                    style.getPropertyValue('overflow-x'));
        }

        function scroll(node) {
            return (/(auto|scroll)/).test(overflow(node));
        }

        return function(node) {
            if (! (node instanceof HTMLElement)) {
                return;
            }

            var ps = parents(node.parentNode, []);

            for (var i = 0; i < ps.length; i += 1) {
                if (scroll(ps[i])) { return ps[i]; }
            }

            return window;
        };
    })();

    /**
    * Basic drag implementation for DOM elements inside a container.
    * Provide start/stop/drag callbacks.
    *
    * @class Draggable
    * @param {HTMLElement} el The HTMLelement that contains all the widgets
    *  to be dragged.
    * @param {Object} [options] An Object with all options you want to
    *        overwrite:
    *    @param {HTMLElement|String} [options.items] Define who will
    *     be the draggable items. Can be a CSS Selector String or a
    *     collection of HTMLElements.
    *    @param {Number} [options.distance] Distance in pixels after mousedown
    *     the mouse must move before dragging should start.
    *    @param {Boolean} [options.limit] Constrains dragging to the width of
    *     the container
    *    @param {Number} [options.offset_left] Offset added to the item
    *    @param {Boolean} [options.autoscroll] Autoscroll when dragging if necessary
    *    @param {Number} [options.scroll_ms] Interval of time in ms during each scroll displacement
    *    @param {Number} [options.scroll_px] Number of px to move the scroll
    *    @param {Number} [options.scroll_trigger_width] Width of the hot areas on
    *     the sides
    *    @param {False|HTMLElement} [options.scroller] The element that contains the scroll.
    *     If not specified is calculated automatically.
    *    @param {Array|Function} [options.ignore_dragging] Array of node names
    *      that sould not trigger dragging, by default is `['INPUT', 'TEXTAREA',
    *      'SELECT', 'BUTTON']`. If a function is used return true to ignore dragging.
    *     that is being dragged.
    *    @param {String} [options.handle] Specify a CSS selector to drag items
    *     using specific elements as handlers.
    *    @param {Null|Number} [options.container_width] If specified, force the width
    *     of the container element.
    *    @param {Boolean} [options.move_element] Move the HTML element when dragging
    *    @param {False|'clone'} [options.helper] If set to 'clone', clone the element
    *     and move the copy instead of the original
    *    @param {False|'clone'} [options.remove_helper] Remove the helper from DOM
    *     when the dragging stops. Only when using opts.helper = 'clone'.
    *    @param {Number} [options.drag] Executes a callback when the mouse is
    *     moved during the dragging.
    *    @param {Number} [options.start] Executes a callback when the drag
    *     starts.
    *    @param {Number} [options.stop] Executes a callback when the drag stops.
    * @return {Object} Returns `el`.
    * @constructor
    */
    function Draggable(el, options) {
      this.options = $.extend({}, defaults, options);
      this.$document = $(document);
      this.$container = $(el);
      this.is_dragging = false;
      this.player_min_left = 0 + this.options.offset_left;
      this.id = uniqId();
      this.ns = '.gridster-draggable-' + this.id;
      this.init();
    }

    Draggable.defaults = defaults;

    var fn = Draggable.prototype;

    fn.init = function() {
        var pos = this.$container.css('position');
        this.$container.css('position', pos === 'static' ? 'relative' : pos);
        this.disabled = false;
        this.events();
        this.setup_scroll();
    };

    fn.nsEvent = function(ev) {
        return (ev || '') + this.ns;
    };

    fn.events = function() {
        this.pointer_events = {
            start: this.nsEvent('touchstart') + ' ' + this.nsEvent('mousedown'),
            move: this.nsEvent('touchmove') + ' ' + this.nsEvent('mousemove'),
            end: this.nsEvent('touchend') + ' ' + this.nsEvent('mouseup')
        };

        this.$container.on(this.nsEvent('selectstart'),
            $.proxy(this.on_select_start, this));

        this.$container.on(this.pointer_events.start, this.options.items,
            $.proxy(this.drag_handler, this));

        this.$document.on(this.pointer_events.end, $.proxy(function(e) {
            this.is_dragging = false;
            if (this.disabled) { return; }
            this.$document.off(this.pointer_events.move);
            if (this.drag_start) {
                this.on_dragstop(e);
            }
        }, this));
    };

    fn.setup_scroll = function() {
        this.$scroller = $window;

        if (!this.options.autoscroll) { return; }

        this.$scroller = $(this.options.scroller ?
            this.options.scroller : scrollParent(this.$container.get(0)));

        $window.bind(this.nsEvent('resize'),
            throttle($.proxy(this.on_resize, this), 200));
    };

    fn.get_actual_pos = function($el) {
        var pos = $el.position();
        return pos;
    };


    fn.get_mouse_pos = function(e) {
        if (e.originalEvent && e.originalEvent.touches) {
            var oe = e.originalEvent;
            e = oe.touches.length ? oe.touches[0] : oe.changedTouches[0];
        }

        return {
            left: e.clientX,
            top: e.clientY
        };
    };


    fn.get_offset = function(e) {
        e.preventDefault();

        var mouse_actual_pos = this.get_mouse_pos(e);
        var diff_x = Math.round(
            mouse_actual_pos.left - this.mouse_init_pos.left);
        var diff_y = Math.round(mouse_actual_pos.top - this.mouse_init_pos.top);

        var scroll_el = this.$scroller.is($window) ? document.body : this.$scroller[0];
        var player_el = this.helper ? this.$helper[0] : this.$player[0];
        var scroller_contains_player = $.contains(scroll_el, player_el);
        var scroll_diff_left = this.$scroller.scrollLeft() - this.init_scroll_left;
        var scroll_diff_top = this.$scroller.scrollTop() - this.init_scroll_top;

        var left = Math.round(this.el_init_offset.left +
            diff_x - this.baseX + scroll_diff_left);
        var top = Math.round(this.el_init_offset.top +
            diff_y - this.baseY + scroll_diff_top);

        if (this.options.limit) {
            if (left > this.player_max_left) {
                left = this.player_max_left;
            } else if(left < this.player_min_left) {
                left = this.player_min_left;
            }
        }

        return {
            position: {
                left: left,
                top: top
            },
            player: {
                left: scroller_contains_player ? left : left - scroll_diff_left,
                top: scroller_contains_player ? top : top - scroll_diff_top
            },
            pointer: {
                left: mouse_actual_pos.left,
                top: mouse_actual_pos.top,
                diff_left: diff_x + scroll_diff_left,
                diff_top: diff_y + scroll_diff_top
            }
        };
    };


    fn.get_drag_data = function(e) {
        var offset = this.get_offset(e);
        offset.$player = this.$player;
        offset.$helper = this.helper ? this.$helper : this.$player;

        return offset;
    };


    fn.set_limits = function(container_width) {
        container_width || (container_width = this.$container.width());
        this.player_max_left = (container_width - this.player_width +
            - this.options.offset_left);

        this.options.container_width = container_width;

        return this;
    };


    fn.manage_scroll = function(e) {
        if (e.fakeEvent) {
            return;
        } else {
            this.clear_scrolls();
        }

        var mouse = this.get_mouse_pos(e);
        var isWindow = this.$scroller[0] === window;
        var mx = mouse.left;
        var my = mouse.top;
        var area_weight = this.options.scroll_trigger_width;
        var scroller = this.scroller;
        var side_to_scroll = {
            left: 'Left',
            right: 'Left',
            top: 'Top',
            bottom: 'Top'
        };
        var hot_areas = {
            top: {
                x: scroller.left,
                y: scroller.top,
                x1: scroller.left + scroller.width,
                y1: scroller.top + area_weight
            },
            right: {
                x: scroller.left + scroller.width - area_weight,
                y: scroller.top,
                x1: scroller.left + scroller.width,
                y1: scroller.top + scroller.height
            },
            bottom: {
                x: scroller.left,
                y: scroller.top + scroller.height - area_weight,
                x1: scroller.left + scroller.width,
                y1: scroller.top + scroller.height
            },
            left: {
                x: scroller.left,
                y: scroller.top,
                x1: scroller.left + area_weight,
                y1: scroller.top + scroller.height
            }
        };

        if (! isWindow) {
            mx += $window.scrollLeft();
            my += $window.scrollTop();
        }

        // store scroll intervals to later clear them
        this.scroll_intervals || (this.scroll_intervals = {});

        $.each(hot_areas, function(side, bounds) {
            var interval = this.scroll_intervals[side];
            var x_movement = (side === 'left' || side === 'right');
            var y_movement = (side === 'top' || side === 'bottom');
            var is_active = (mx >= bounds.x && mx <= bounds.x1 &&
                             my >= bounds.y && my <= bounds.y1);

            // clean current setIntervals for non hovered areas
            if (!is_active) {
                interval && clearInterval(interval);
                this.scroll_intervals[side] = null;
                return;
            }

            // if no setIntervals for hovered areas, set them
            if (is_active && !interval) {
                this.scroll_intervals[side] = setInterval(function() {
                    var scroll_method = 'scroll' + side_to_scroll[side];
                    var dir = (side === 'left' || side === 'top') ? -1 : 1;
                    var offset = this.options.scroll_px * dir;

                    // scroll view
                    if (isWindow) {
                        var scroll_by = x_movement ? [offset, 0] : [0, offset];
                        this.$scroller[0].scrollBy.apply(this.$scroller[0], scroll_by);
                    } else {
                        this.$scroller[0][scroll_method] += offset;
                    }

                    // trigger mousemove event
                    var mme = $.Event('mousemove');
                    mme.clientX = mouse.left + (x_movement ? offset : 0);
                    mme.clientY = mouse.top + (y_movement ? offset : 0);
                    mme.fakeEvent = true;
                    this.$document.trigger(mme);
                }.bind(this), this.options.scroll_ms);
            }
        }.bind(this));
    };

    fn.clear_scrolls = function() {
        if (!this.scroll_intervals) { return; }
        $.each(this.scroll_intervals, function(side) {
            clearInterval(this.scroll_intervals[side]);
            this.scroll_intervals[side] = null;
        }.bind(this));
    };


    fn.drag_handler = function(e) {
        // skip if drag is disabled, or click was not done with the mouse primary button
        if (this.disabled || e.which !== 1 && !isTouch) {
            return;
        }

        if (this.ignore_drag(e)) {
            return;
        }

        var self = this;
        var first = true;
        this.$player = $(e.currentTarget);

        this.el_init_pos = this.get_actual_pos(this.$player);
        this.mouse_init_pos = this.get_mouse_pos(e);
        this.offsetY = this.mouse_init_pos.top - this.el_init_pos.top;

        this.$document.on(this.pointer_events.move, function(mme) {
            var mouse_actual_pos = self.get_mouse_pos(mme);
            var diff_x = Math.abs(
                mouse_actual_pos.left - self.mouse_init_pos.left);
            var diff_y = Math.abs(
                mouse_actual_pos.top - self.mouse_init_pos.top);
            if (!(diff_x > self.options.distance ||
                diff_y > self.options.distance)
                ) {
                return false;
            }

            if (first) {
                first = false;
                self.on_dragstart.call(self, mme);
                return false;
            }

            if (self.is_dragging === true) {
                self.on_dragmove.call(self, mme);
            }

            return false;
        });

        if (!isTouch) { return false; }
    };


    fn.on_dragstart = function(e) {
        e.preventDefault();

        if (this.is_dragging) { return this; }

        this.drag_start = this.is_dragging = true;
        var offset = this.$container.offset();
        this.baseX = Math.round(offset.left);
        this.baseY = Math.round(offset.top);

        if (this.options.autoscroll) {
            this.calculate_scroll_dimensions();
        }

        if (this.options.helper === 'clone') {
            this.$helper = this.$player.clone()
                .appendTo(this.$container).addClass('helper');
            this.helper = true;
        } else {
            this.helper = false;
        }

        this.init_scroll_top = this.$scroller.scrollTop();
        this.init_scroll_left = this.$scroller.scrollLeft();
        this.el_init_offset = this.$player.offset();
        this.player_width = this.$player.width();

        this.set_limits(this.options.container_width);

        if (this.options.start) {
            this.options.start.call(this.$player, e, this.get_drag_data(e));
        }
        return false;
    };


    fn.on_dragmove = function(e) {
        var data = this.get_drag_data(e);

        this.options.autoscroll && this.manage_scroll(e);

        if (this.options.move_element) {
            (this.helper ? this.$helper : this.$player).css({
                'position': 'absolute',
                'left' : data.player.left,
                'top' : data.player.top
            });
        }

        var last_position = this.last_position || data.position;
        data.prev_position = last_position;

        if (this.options.drag) {
            this.options.drag.call(this.$player, e, data);
        }

        this.last_position = data.position;
        return false;
    };


    fn.on_dragstop = function(e) {
        var data = this.get_drag_data(e);
        this.drag_start = false;

        this.clear_scrolls();

        if (this.options.stop) {
            this.options.stop.call(this.$player, e, data);
        }

        if (this.helper && this.options.remove_helper) {
            this.$helper.remove();
        }

        return false;
    };

    fn.on_select_start = function(e) {
        if (this.disabled) { return; }

        if (this.ignore_drag(e)) {
            return;
        }

        return false;
    };


    fn.calculate_scroll_dimensions = function() {
        this.scroller = this.$scroller.is($window) ?
                {top: 0, left: 0} : this.$scroller.offset();

        this.scroller.width = this.$scroller.width();
        this.scroller.height = this.$scroller.height();
    };

    fn.on_resize = function(e) {
        if (this.options.autoscroll) {
            this.calculate_scroll_dimensions();
        }
    };

    fn.enable = function() {
        this.disabled = false;
    };

    fn.disable = function() {
        this.disabled = true;
    };

    fn.destroy = function() {
        this.disable();

        this.$container.off(this.ns);
        this.$document.off(this.ns);
        $window.off(this.ns);

        $.removeData(this.$container, 'drag');
    };

    fn.ignore_drag = function(event) {
        if (this.options.handle) {
            return !$(event.target).is(this.options.handle);
        }

        if ($.isFunction(this.options.ignore_dragging)) {
            return this.options.ignore_dragging(event);
        }

        return $(event.target).is(this.options.ignore_dragging.join(', '));
    };

    //jQuery adapter
    $.fn.drag = function ( options ) {
        return new Draggable(this, options);
    };

    return Draggable;

}));
