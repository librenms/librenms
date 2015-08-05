/*! gridster.js - v0.6.10 - 2015-08-05
* https://dsmorse.github.io/gridster.js/
* Copyright (c) 2015 ducksboard; Licensed MIT */

;(function(root, factory) {
	'use strict';
    if(typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    }
    else if (typeof define === 'function' && define.amd) {
        define('gridster-coords', ['jquery'], factory);
    } else {
       root.GridsterCoords = factory(root.$ || root.jQuery);
    }

}(this, function($) {
	'use strict';
    /**
    * Creates objects with coordinates (x1, y1, x2, y2, cx, cy, width, height)
    * to simulate DOM elements on the screen.
    * Coords is used by Gridster to create a faux grid with any DOM element can
    * collide.
    *
    * @class Coords
    * @param {HTMLElement|Object} obj The jQuery HTMLElement or a object with: left,
    * top, width and height properties.
    * @return {Object} Coords instance.
    * @constructor
    */
    function Coords(obj) {
        if (obj[0] && $.isPlainObject(obj[0])) {
            this.data = obj[0];
        }else {
            this.el = obj;
        }

        this.isCoords = true;
        this.coords = {};
        this.init();
        return this;
    }


    var fn = Coords.prototype;


    fn.init = function(){
        this.set();
        this.original_coords = this.get();
    };


    fn.set = function(update, not_update_offsets) {
        var el = this.el;

        if (el && !update) {
            this.data = el.offset();
            this.data.width = el[0].scrollWidth;
            this.data.height = el[0].scrollHeight;
        }

        if (el && update && !not_update_offsets) {
            var offset = el.offset();
            this.data.top = offset.top;
            this.data.left = offset.left;
        }

        var d = this.data;

        if ( d.left === undefined ) {
            d.left = d.x1;
        }

        if ( d.top === undefined ) {
            d.top = d.y1;
        }

        this.coords.x1 = d.left;
        this.coords.y1 = d.top;
        this.coords.x2 = d.left + d.width;
        this.coords.y2 = d.top + d.height;
        this.coords.cx = d.left + (d.width / 2);
        this.coords.cy = d.top + (d.height / 2);
        this.coords.width  = d.width;
        this.coords.height = d.height;
        this.coords.el  = el || false ;

        return this;
    };


    fn.update = function(data){
        if (!data && !this.el) {
            return this;
        }

        if (data) {
            var new_data = $.extend({}, this.data, data);
            this.data = new_data;
            return this.set(true, true);
        }

        this.set(true);
        return this;
    };


    fn.get = function(){
        return this.coords;
    };

    fn.destroy = function() {
        this.el.removeData('coords');
        delete this.el;
    };

    //jQuery adapter
    $.fn.coords = function() {
        if (this.data('coords') ) {
            return this.data('coords');
        }

        var ins = new Coords(this);
        this.data('coords', ins);
        return ins;
    };

    return Coords;

}));

;(function(root, factory) {
	'use strict';
    if(typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    }
    else if (typeof define === 'function' && define.amd) {
        define('gridster-collision', ['jquery', 'gridster-coords'], factory);
    } else {
        root.GridsterCollision = factory(root.$ || root.jQuery,
            root.GridsterCoords);
    }

}(this, function($, Coords) {
	'use strict';
    var defaults = {
        colliders_context: document.body,
        overlapping_region: 'C'
        // ,on_overlap: function(collider_data){},
        // on_overlap_start : function(collider_data){},
        // on_overlap_stop : function(collider_data){}
    };


    /**
    * Detects collisions between a DOM element against other DOM elements or
    * Coords objects.
    *
    * @class Collision
    * @uses Coords
    * @param {HTMLElement} el The jQuery wrapped HTMLElement.
    * @param {HTMLElement|Array} colliders Can be a jQuery collection
    *  of HTMLElements or an Array of Coords instances.
    * @param {Object} [options] An Object with all options you want to
    *        overwrite:
    *   @param {String} [options.overlapping_region] Determines when collision
    *    is valid, depending on the overlapped area. Values can be: 'N', 'S',
    *    'W', 'E', 'C' or 'all'. Default is 'C'.
    *   @param {Function} [options.on_overlap_start] Executes a function the first
    *    time each `collider ` is overlapped.
    *   @param {Function} [options.on_overlap_stop] Executes a function when a
    *    `collider` is no longer collided.
    *   @param {Function} [options.on_overlap] Executes a function when the
    * mouse is moved during the collision.
    * @return {Object} Collision instance.
    * @constructor
    */
    function Collision(el, colliders, options) {
        this.options = $.extend(defaults, options);
        this.$element = el;
        this.last_colliders = [];
        this.last_colliders_coords = [];
        this.set_colliders(colliders);

        this.init();
    }

    Collision.defaults = defaults;

    var fn = Collision.prototype;


    fn.init = function() {
        this.find_collisions();
    };


    fn.overlaps = function(a, b) {
        var x = false;
        var y = false;

        if ((b.x1 >= a.x1 && b.x1 <= a.x2) ||
            (b.x2 >= a.x1 && b.x2 <= a.x2) ||
            (a.x1 >= b.x1 && a.x2 <= b.x2)
        ) { x = true; }

        if ((b.y1 >= a.y1 && b.y1 <= a.y2) ||
            (b.y2 >= a.y1 && b.y2 <= a.y2) ||
            (a.y1 >= b.y1 && a.y2 <= b.y2)
        ) { y = true; }

        return (x && y);
    };


    fn.detect_overlapping_region = function(a, b){
        var regionX = '';
        var regionY = '';

        if (a.y1 > b.cy && a.y1 < b.y2) { regionX = 'N'; }
        if (a.y2 > b.y1 && a.y2 < b.cy) { regionX = 'S'; }
        if (a.x1 > b.cx && a.x1 < b.x2) { regionY = 'W'; }
        if (a.x2 > b.x1 && a.x2 < b.cx) { regionY = 'E'; }

        return (regionX + regionY) || 'C';
    };


    fn.calculate_overlapped_area_coords = function(a, b){
        var x1 = Math.max(a.x1, b.x1);
        var y1 = Math.max(a.y1, b.y1);
        var x2 = Math.min(a.x2, b.x2);
        var y2 = Math.min(a.y2, b.y2);

        return $({
            left: x1,
            top: y1,
             width : (x2 - x1),
            height: (y2 - y1)
          }).coords().get();
    };


    fn.calculate_overlapped_area = function(coords){
        return (coords.width * coords.height);
    };


    fn.manage_colliders_start_stop = function(new_colliders_coords, start_callback, stop_callback){
        var last = this.last_colliders_coords;

        for (var i = 0, il = last.length; i < il; i++) {
            if ($.inArray(last[i], new_colliders_coords) === -1) {
                start_callback.call(this, last[i]);
            }
        }

        for (var j = 0, jl = new_colliders_coords.length; j < jl; j++) {
            if ($.inArray(new_colliders_coords[j], last) === -1) {
                stop_callback.call(this, new_colliders_coords[j]);
            }

        }
    };


    fn.find_collisions = function(player_data_coords){
        var self = this;
        var overlapping_region = this.options.overlapping_region;
        var colliders_coords = [];
        var colliders_data = [];
        var $colliders = (this.colliders || this.$colliders);
        var count = $colliders.length;
        var player_coords = self.$element.coords()
                             .update(player_data_coords || false).get();

        while(count--){
          var $collider = self.$colliders ?
                           $($colliders[count]) : $colliders[count];
          var $collider_coords_ins = ($collider.isCoords) ?
                  $collider : $collider.coords();
          var collider_coords = $collider_coords_ins.get();
          var overlaps = self.overlaps(player_coords, collider_coords);

          if (!overlaps) {
            continue;
          }

          var region = self.detect_overlapping_region(
              player_coords, collider_coords);

            //todo: make this an option
            if (region === overlapping_region || overlapping_region === 'all') {

                var area_coords = self.calculate_overlapped_area_coords(
                    player_coords, collider_coords);
                var area = self.calculate_overlapped_area(area_coords);
                if ( 0 !== area ) {
                    var collider_data = {
                        area: area,
                        area_coords : area_coords,
                        region: region,
                        coords: collider_coords,
                        player_coords: player_coords,
                        el: $collider
                    };

                    if (self.options.on_overlap) {
                        self.options.on_overlap.call(this, collider_data);
                    }
                    colliders_coords.push($collider_coords_ins);
                    colliders_data.push(collider_data);
                }
            }
        }

        if (self.options.on_overlap_stop || self.options.on_overlap_start) {
            this.manage_colliders_start_stop(colliders_coords,
                self.options.on_overlap_start, self.options.on_overlap_stop);
        }

        this.last_colliders_coords = colliders_coords;

        return colliders_data;
    };


    fn.get_closest_colliders = function(player_data_coords){
        var colliders = this.find_collisions(player_data_coords);

        colliders.sort(function(a, b) {
            /* if colliders are being overlapped by the "C" (center) region,
             * we have to set a lower index in the array to which they are placed
             * above in the grid. */
            if (a.region === 'C' && b.region === 'C') {
                if (a.coords.y1 < b.coords.y1 || a.coords.x1 < b.coords.x1) {
                    return - 1;
                }else{
                    return 1;
                }
            }

            if (a.area < b.area) {
                return 1;
            }

            return 1;
        });
        return colliders;
    };


    fn.set_colliders = function(colliders) {
        if (typeof colliders === 'string' || colliders instanceof $) {
            this.$colliders = $(colliders,
                 this.options.colliders_context).not(this.$element);
        }else{
            this.colliders = $(colliders);
        }
    };


    //jQuery adapter
    $.fn.collision = function(collider, options) {
          return new Collision( this, collider, options );
    };

    return Collision;

}));

(function (window, undefined) {
	'use strict';
	/* Delay, debounce and throttle functions taken from underscore.js
	 *
	 * Copyright (c) 2009-2013 Jeremy Ashkenas, DocumentCloud and
	 * Investigative Reporters & Editors
	 *
	 * Permission is hereby granted, free of charge, to any person
	 * obtaining a copy of this software and associated documentation
	 * files (the "Software"), to deal in the Software without
	 * restriction, including without limitation the rights to use,
	 * copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the
	 * Software is furnished to do so, subject to the following
	 * conditions:
	 *
	 * The above copyright notice and this permission notice shall be
	 * included in all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
	 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
	 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	 * OTHER DEALINGS IN THE SOFTWARE.
	 */

	window.delay = function (func, wait) {
		var args = Array.prototype.slice.call(arguments, 2);
		return setTimeout(function () {
			return func.apply(null, args);
		}, wait);
	};

	window.debounce = function (func, wait, immediate) {
		var timeout;
		return function () {
			var context = this, args = arguments;
			var later = function () {
				timeout = null;
				if (!immediate) {
					func.apply(context, args);
				}
			};
			if (immediate && !timeout) {
				func.apply(context, args);
			}
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	};

	window.throttle = function (func, wait) {
		var context, args, timeout, throttling, more, result;
		var whenDone = debounce(
				function () {
					more = throttling = false;
				}, wait);
		return function () {
			context = this;
			args = arguments;
			var later = function () {
				timeout = null;
				if (more) {
					func.apply(context, args);
				}
				whenDone();
			};
			if (!timeout) {
				timeout = setTimeout(later, wait);
			}
			if (throttling) {
				more = true;
			} else {
				result = func.apply(context, args);
			}
			whenDone();
			throttling = true;
			return result;
		};
	};

})(window);

;(function(root, factory) {
 'use strict';
    if(typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    }
    else if (typeof define === 'function' && define.amd) {
        define('gridster-draggable', ['jquery'], factory);
    } else {
        root.GridsterDraggable = factory(root.$ || root.jQuery);
    }

}(this, function($) {
	'use strict';
    var defaults = {
        items: 'li',
        distance: 1,
        limit: true,
        offset_left: 0,
        autoscroll: true,
        ignore_dragging: ['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON'], // or function
        handle: null,
        container_width: 0,  // 0 == auto
        move_element: true,
        helper: false,  // or 'clone'
        remove_helper: true
        // drag: function(e) {},
        // start : function(e, ui) {},
        // stop : function(e) {}
    };

    var $window = $(window);
    var dir_map = { x : 'left', y : 'top' };
    var isTouch = !!('ontouchstart' in window);

    var capitalize = function(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    };

    var idCounter = 0;
    var uniqId = function() {
        return ++idCounter + '';
    };

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
    *    @param {Object|Function} [options.ignore_dragging] Array of node names
    *      that sould not trigger dragging, by default is `['INPUT', 'TEXTAREA',
    *      'SELECT', 'BUTTON']`. If a function is used return true to ignore dragging.
    *    @param {offset_left} [options.offset_left] Offset added to the item
    *     that is being dragged.
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
        this.$scroll_container = this.options.scroll_container === window ?
            $(window) : this.$container.closest(this.options.scroll_container);
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
        this.calculate_dimensions();
        this.$container.css('position', pos === 'static' ? 'relative' : pos);
        this.disabled = false;
        this.events();

        $window.bind(this.nsEvent('resize'),
				throttle($.proxy(this.calculate_dimensions, this), 200));
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

    fn.get_actual_pos = function($el) {
		return $el.position();
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

        var left = Math.round(this.el_init_offset.left +
                              diff_x - this.baseX +
                              this.$scroll_container.scrollLeft() -
                              this.scroll_container_offset_x);
        var top = Math.round(this.el_init_offset.top +
                             diff_y - this.baseY +
                             this.$scroll_container.scrollTop() -
                             this.scroll_container_offset_y);

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
            pointer: {
                left: mouse_actual_pos.left,
                top: mouse_actual_pos.top,
                diff_left: diff_x + (this.$scroll_container.scrollLeft() -
                           this.scroll_container_offset_x),
                diff_top: diff_y + (this.$scroll_container.scrollTop() -
                          this.scroll_container_offset_y)
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


    fn.scroll_in = function(axis, data) {
        var dir_prop = dir_map[axis];

        var area_size = 50;
        var scroll_inc = 30;
        var scrollDir = 'scroll' + capitalize(dir_prop);

        var is_x = axis === 'x';
        var scroller_size = is_x ? this.scroller_width : this.scroller_height;
        var doc_size;
        if (this.$scroll_container === window){
            doc_size = is_x ? this.$scroll_container.width() :
                              this.$scroll_container.height();
        }else{
            doc_size = is_x ? this.$scroll_container[0].scrollWidth :
                              this.$scroll_container[0].scrollHeight;
        }
        var player_size = is_x ? this.$player.width() : this.$player.height();

        var next_scroll;
        var scroll_offset = this.$scroll_container[scrollDir]();
        var min_scroll_pos = scroll_offset;
        var max_scroll_pos = min_scroll_pos + scroller_size;

        var mouse_next_zone = max_scroll_pos - area_size;  // down/right
        var mouse_prev_zone = min_scroll_pos + area_size;  // up/left

        var abs_mouse_pos = min_scroll_pos + data.pointer[dir_prop];

        var max_player_pos = (doc_size - scroller_size + player_size);

        if (abs_mouse_pos >= mouse_next_zone) {
            next_scroll = scroll_offset + scroll_inc;
            if (next_scroll < max_player_pos) {
                this.$scroll_container[scrollDir](next_scroll);
                this['scroll_offset_' + axis] += scroll_inc;
            }
        }

        if (abs_mouse_pos <= mouse_prev_zone) {
            next_scroll = scroll_offset - scroll_inc;
            if (next_scroll > 0) {
                this.$scroll_container[scrollDir](next_scroll);
                this['scroll_offset_' + axis] -= scroll_inc;
            }
        }

        return this;
    };


    fn.manage_scroll = function(data) {
        this.scroll_in('x', data);
        this.scroll_in('y', data);
    };


    fn.calculate_dimensions = function() {
        this.scroller_height = this.$scroll_container.height();
        this.scroller_width = this.$scroll_container.width();
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

        if (this.options.helper === 'clone') {
            this.$helper = this.$player.clone()
                .appendTo(this.$container).addClass('helper');
            this.helper = true;
        } else {
            this.helper = false;
        }

        this.scroll_container_offset_y = this.$scroll_container.scrollTop();
        this.scroll_container_offset_x = this.$scroll_container.scrollLeft();
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

        this.options.autoscroll && this.manage_scroll(data);

        if (this.options.move_element) {
            (this.helper ? this.$helper : this.$player).css({
                'position': 'absolute',
                'left' : data.position.left,
                'top' : data.position.top
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

        if (this.options.resize) {
            return ! $(event.target).is(this.options.items);
        }

        return $(event.target).is(this.options.ignore_dragging.join(', '));
    };

    //jQuery adapter
    $.fn.gridDraggable = function ( options ) {
        return new Draggable(this, options);
    };

	$.fn.dragg = function (options) {
		return this.each(function () {
			if (!$.data(this, 'drag')) {
				$.data(this, 'drag', new Draggable(this, options));
			}
		});
	};

    return Draggable;

}));

(function (root, factory) {
	'use strict';
	if (typeof exports === 'object') {
		module.exports = factory(require('jquery'), require('./jquery.draggable.js'), require('./jquery.collision.js'), require('./jquery.coords.js'), require('./utils.js'));
	}
	else if (typeof define === 'function' && define.amd) {
		define(['jquery', 'gridster-draggable', 'gridster-collision'], factory);
	} else {
		root.Gridster = factory(root.$ || root.jQuery, root.GridsterDraggable,
				root.GridsterCollision);
	}

}(this, function ($, Draggable, Collision) {
	'use strict';
	var $window = $(window),
			defaults = {
				namespace: '',
				widget_selector: 'li',
				static_class: 'static',
				widget_margins: [10, 10],
				widget_base_dimensions: [400, 225],
				extra_rows: 0,
				extra_cols: 0,
				min_cols: 1,
				max_cols: Infinity,
				min_rows: 1,
				max_rows: 15,
				autogenerate_stylesheet: true,
				avoid_overlapped_widgets: true,
				auto_init: true,
				center_widgets: false,
				responsive_breakpoint: false,
				scroll_container: window,
				shift_larger_widgets_down: true,
				shift_widgets_up: true,
				show_element: function($el, callback) {
					if (callback) {
						$el.fadeIn(callback);
					} else {
						$el.fadeIn();
					}
				},
				hide_element: function($el, callback) {
					if (callback) {
						$el.fadeOut(callback);
					} else {
						$el.fadeOut();
					}
				},
				serialize_params: function($w, wgd) {
					return {
						col: wgd.col,
						row: wgd.row,
						size_x: wgd.size_x,
						size_y: wgd.size_y
					};
				},
				collision: {
					wait_for_mouseup: false
				},
				draggable: {
					items: '.gs-w:not(.static)',
					distance: 4,
					ignore_dragging: Draggable.defaults.ignore_dragging.slice(0)
				},
				resize: {
					enabled: false,
					axes: ['both'],
					handle_append_to: '',
					handle_class: 'gs-resize-handle',
					max_size: [Infinity, Infinity],
					min_size: [1, 1]
				}
			};

	/**
	 * @class Gridster
	 * @uses Draggable
	 * @uses Collision
	 * @param {HTMLElement} el The HTMLelement that contains all the widgets.
	 * @param {Object} [options] An Object with all options you want to
	 *        overwrite:
	 *        @param {HTMLElement|String} [options.widget_selector] Define who will
	 *            be the draggable widgets. Can be a CSS Selector String or a
	 *            collection of HTMLElements
	 *        @param {Array} [options.widget_margins] Margin between widgets.
	 *            The first index for the horizontal margin (left, right) and
	 *            the second for the vertical margin (top, bottom).
	 *        @param {Array} [options.widget_base_dimensions] Base widget dimensions
	 *            in pixels. The first index for the width and the second for the
	 *            height.
	 *        @param {Number} [options.extra_cols] Add more columns in addition to
	 *            those that have been calculated.
	 *        @param {Number} [options.extra_rows] Add more rows in addition to
	 *            those that have been calculated.
	 *        @param {Number} [options.min_cols] The minimum required columns.
	 *        @param {Number} [options.max_cols] The maximum columns possible (set to null
	 *            for no maximum).
	 *        @param {Number} [options.min_rows] The minimum required rows.
	 *        @param {Boolean} [options.autogenerate_stylesheet] If true, all the
	 *            CSS required to position all widgets in their respective columns
	 *            and rows will be generated automatically and injected to the
	 *            `<head>` of the document. You can set this to false, and write
	 *            your own CSS targeting rows and cols via data-attributes like so:
	 *            `[data-col="1"] { left: 10px; }`
	 *        @param {Boolean} [options.avoid_overlapped_widgets] Avoid that widgets loaded
	 *            from the DOM can be overlapped. It is helpful if the positions were
	 *            bad stored in the database or if there was any conflict.
	 *        @param {Boolean} [options.auto_init] Automatically call gridster init
	 *            method or not when the plugin is instantiated.
	 *        @param {Function} [options.serialize_params] Return the data you want
	 *            for each widget in the serialization. Two arguments are passed:
	 *            `$w`: the jQuery wrapped HTMLElement, and `wgd`: the grid
	 *            coords object (`col`, `row`, `size_x`, `size_y`).
	 *        @param {Boolean} [options.shift_larger_widgets_down] Determines if how widgets get pushes
	 *            out of the way of the player. If set to false smaller widgets will not move larger
	 *            widgets out of their way . Defaults to true.
	 *        @param {Boolean} [options.shift_widgets_up] Determines if the player will automatically
	 *            condense the grid and not allow a widget to have space above it. Defaults to true.
	 *        @param {Function} [options.show_element] Makes the given element visible. Two arguments are passed:
	 *            `$el`: the jQuery wrapped HTMLElement, and `callback`: a function that is executed
	 *            after the element is made visible. The callback parameter is optional.
	 *        @param {Function} [options.hide_element] Hides the given element. Two arguments are passed:
	 *            `$el`: the jQuery wrapped HTMLElement, and `callback`: a function that is executed
	 *            after the element is hidden. The callback parameter is optional.
	 *        @param {Object} [options.collision] An Object with all options for
	 *            Collision class you want to overwrite. See Collision docs for
	 *            more info.
	 *                  @param {Boolean} [options.collision.wait_for_mouseup] Default is false.
	 *                       If true then it will not move colliding widgets during drag, but only on
	 *                       mouseup.
	 *        @param {Object} [options.draggable] An Object with all options for
	 *            Draggable class you want to overwrite. See Draggable docs for more info.
	 *                @param {Object|Function} [options.draggable.ignore_dragging] Note that
	 *                    if you use a Function, and resize is enabled, you should ignore the
	 *                    resize handlers manually (options.resize.handle_class).
	 *        @param {Object} [options.resize] An Object with resize config options.
	 *                @param {Boolean} [options.resize.enabled] Set to true to enable
	 *                    resizing.
	 *                @param {Array} [options.resize.axes] Axes in which widgets can be
	 *                    resized. Possible values: ['x', 'y', 'both'].
	 *                @param {String} [options.resize.handle_append_to] Set a valid CSS
	 *                    selector to append resize handles to.
	 *                @param {String} [options.resize.handle_class] CSS class name used
	 *                    by resize handles.
	 *                @param {Array} [options.resize.max_size] Limit widget dimensions
	 *                    when resizing. Array values should be integers:
	 *                    `[max_cols_occupied, max_rows_occupied]`
	 *                @param {Array} [options.resize.min_size] Limit widget dimensions
	 *                    when resizing. Array values should be integers:
	 *                    `[min_cols_occupied, min_rows_occupied]`
	 *                @param {Function} [options.resize.start] Function executed
	 *                    when resizing starts.
	 *                @param {Function} [options.resize.resize] Function executed
	 *                    during the resizing.
	 *                @param {Function} [options.resize.stop] Function executed
	 *                    when resizing stops.
	 *
	 * @constructor
	 */
	function Gridster (el, options) {
		this.options = $.extend(true, {}, defaults, options);
		this.options.draggable = this.options.draggable || {};
		this.options.draggable = $.extend(true, {}, this.options.draggable,
				{scroll_container: this.options.scroll_container});
		this.$el = $(el);
		this.$scroll_container = this.options.scroll_container === window ?
				$(window) : this.$el.closest(this.options.scroll_container);
		this.$wrapper = this.$el.parent();
		this.$widgets = this.$el.children(this.options.widget_selector).addClass('gs-w');
		this.$changed = $([]);
		this.w_queue = {};
		if (this.is_responsive()) {
			this.min_widget_width = this.get_responsive_col_width();
		} else {
			this.min_widget_width = this.options.widget_base_dimensions[0];
		}
		this.min_widget_height = this.options.widget_base_dimensions[1];

		this.min_col_count = this.options.min_cols;
		this.prev_col_count = this.min_col_count;

		this.generated_stylesheets = [];
		this.$style_tags = $([]);

		this.options.auto_init && this.init();
	}

	Gridster.defaults = defaults;
	Gridster.generated_stylesheets = [];

	/**
	 * Convert properties to Integer
	 *
	 * @param {Object} obj - config object to be converted
	 * @return {Object} Returns the converted object.
	 */
	function convInt (obj) {
		var props = ['col', 'row', 'size_x', 'size_y'];
		var tmp = {};
		for (var i = 0, len = props.length; i < len; i++) {
			var prop = props[i];
			if (!(prop in obj)) {
				throw new Error('Not exists property `' + prop + '`');
			}
			var val = obj[prop];
			if (!val || isNaN(val)) {
				throw new Error('Invalid value of `' + prop + '` property');
			}
			tmp[prop] = +val;
		}
		return tmp;
	}

	/**
	 * Sorts an Array of grid coords objects (representing the grid coords of
	 * each widget) in ascending way.
	 *
	 * @method sort_by_row_asc
	 * @param {Array} widgets Array of grid coords objects
	 * @return {Array} Returns the array sorted.
	 */
	Gridster.sort_by_row_asc = function (widgets) {
		widgets = widgets.sort(function (a, b) {
			if (!a.row) {
				a = $(a).coords().grid;
				b = $(b).coords().grid;
			}

			a = convInt(a);
			b = convInt(b);
			if (a.row > b.row) {
				return 1;
			}
			return -1;
		});

		return widgets;
	};


	/**
	 * Sorts an Array of grid coords objects (representing the grid coords of
	 * each widget) placing first the empty cells upper left.
	 *
	 * @method sort_by_row_and_col_asc
	 * @param {Array} widgets Array of grid coords objects
	 * @return {Array} Returns the array sorted.
	 */
	Gridster.sort_by_row_and_col_asc = function (widgets) {
		widgets = widgets.sort(function (a, b) {
			a = convInt(a);
			b = convInt(b);
			if (a.row > b.row || a.row === b.row && a.col > b.col) {
				return 1;
			}
			return -1;
		});

		return widgets;
	};


	/**
	 * Sorts an Array of grid coords objects by column (representing the grid
	 * coords of each widget) in ascending way.
	 *
	 * @method sort_by_col_asc
	 * @param {Array} widgets Array of grid coords objects
	 * @return {Array} Returns the array sorted.
	 */
	Gridster.sort_by_col_asc = function (widgets) {
		widgets = widgets.sort(function (a, b) {
			a = convInt(a);
			b = convInt(b);
			if (a.col > b.col) {
				return 1;
			}
			return -1;
		});

		return widgets;
	};


	/**
	 * Sorts an Array of grid coords objects (representing the grid coords of
	 * each widget) in descending way.
	 *
	 * @method sort_by_row_desc
	 * @param {Array} widgets Array of grid coords objects
	 * @return {Array} Returns the array sorted.
	 */
	Gridster.sort_by_row_desc = function (widgets) {
		widgets = widgets.sort(function (a, b) {
			a = convInt(a);
			b = convInt(b);
			if (a.row + a.size_y < b.row + b.size_y) {
				return 1;
			}
			return -1;
		});
		return widgets;
	};


	/** Instance Methods **/

	var fn = Gridster.prototype;

	fn.init = function () {
		this.options.resize.enabled && this.setup_resize();
		this.generate_grid_and_stylesheet();
		this.get_widgets_from_DOM();
		this.set_dom_grid_height();
		this.set_dom_grid_width();
		this.$wrapper.addClass('ready');
		this.draggable();
		this.options.resize.enabled && this.resizable();

		if (this.options.center_widgets) {
			setTimeout($.proxy(function () {
				this.center_widgets();
			}, this), 0);
		}

		$window.bind('resize.gridster', throttle(
				$.proxy(this.recalculate_faux_grid, this), 200));
	};


	/**
	 * Disables dragging.
	 *
	 * @method disable
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.disable = function () {
		this.$wrapper.find('.player-revert').removeClass('player-revert');
		this.drag_api.disable();
		return this;
	};


	/**
	 * Enables dragging.
	 *
	 * @method enable
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.enable = function () {
		this.drag_api.enable();
		return this;
	};


	/**
	 * Disables drag-and-drop widget resizing.
	 *
	 * @method disable
	 * @return {Gridster} Returns instance of gridster Class.
	 */
	fn.disable_resize = function () {
		this.$el.addClass('gs-resize-disabled');
		this.resize_api.disable();
		return this;
	};


	/**
	 * Enables drag-and-drop widget resizing.
	 *
	 * @method enable
	 * @return {Gridster} Returns instance of gridster Class.
	 */
	fn.enable_resize = function () {
		this.$el.removeClass('gs-resize-disabled');
		this.resize_api.enable();
		return this;
	};


	/**
	 * Add a new widget to the grid.
	 *
	 * @method add_widget
	 * @param {String|HTMLElement} html The string representing the HTML of the widget
	 *  or the HTMLElement.
	 * @param {Number} [size_x] The nº of rows the widget occupies horizontally.
	 * @param {Number} [size_y] The nº of columns the widget occupies vertically.
	 * @param {Number} [col] The column the widget should start in.
	 * @param {Number} [row] The row the widget should start in.
	 * @param {Array} [max_size] max_size Maximun size (in units) for width and height.
	 * @param {Array} [min_size] min_size Minimum size (in units) for width and height.
	 * @param {Function} [callback] Function executed after the widget is shown.
	 * @return {HTMLElement} Returns the jQuery wrapped HTMLElement representing.
	 *  the widget that was just created.
	 */
	fn.add_widget = function (html, size_x, size_y, col, row, max_size, min_size, callback) {
		var pos;
		size_x || (size_x = 1);
		size_y || (size_y = 1);

		if (!col && !row) {
			pos = this.next_position(size_x, size_y);
		} else {
			pos = {
				col: col,
				row: row,
				size_x: size_x,
				size_y: size_y
			};
			if (this.options.avoid_overlapped_widgets) {
				this.empty_cells(col, row, size_x, size_y);
			}
		}

		var $w = $(html).attr({
			'data-col': pos.col,
			'data-row': pos.row,
			'data-sizex': size_x,
			'data-sizey': size_y
		}).addClass('gs-w').appendTo(this.$el).hide();

		this.$widgets = this.$widgets.add($w);
		this.$changed = this.$changed.add($w);

		this.register_widget($w);

		var reqRows = parseInt(pos.row) + (parseInt(pos.size_y) - 1);
		if (this.rows < reqRows) {
			this.add_faux_rows(reqRows - this.rows);
		}

		if (max_size) {
			this.set_widget_max_size($w, max_size);
		}

		if (min_size) {
			this.set_widget_min_size($w, min_size);
		}

		this.set_dom_grid_width();
		this.set_dom_grid_height();

		this.drag_api.set_limits((this.cols * this.min_widget_width) + ((this.cols + 1) * this.options.widget_margins[0]));

		if (this.options.center_widgets) {
			setTimeout($.proxy(function () {
				this.center_widgets();
			}, this), 0);
		}

		this.options.show_element.call(this, $w, callback);
		
		return $w;
	};


	/**
	 * Change widget size limits.
	 *
	 * @method set_widget_min_size
	 * @param {HTMLElement|Number} $widget The jQuery wrapped HTMLElement
	 *  representing the widget or an index representing the desired widget.
	 * @param {Array} min_size Minimum size (in grid units) for width and height.
	 * @return {Gridster} Returns instance of gridster Class.
	 */
	fn.set_widget_min_size = function ($widget, min_size) {
		$widget = typeof $widget === 'number' ?
				this.$widgets.eq($widget) : $widget;

		if (!$widget.length) {
			return this;
		}

		var wgd = $widget.data('coords').grid;
		wgd.min_size_x = min_size[0];
		wgd.min_size_y = min_size[1];

		return this;
	};


	/**
	 * Change widget size limits.
	 *
	 * @method set_widget_max_size
	 * @param {HTMLElement|Number} $widget The jQuery wrapped HTMLElement
	 *  representing the widget or an index representing the desired widget.
	 * @param {Array} max_size Maximun size (in units) for width and height.
	 * @return {Gridster} Returns instance of gridster Class.
	 */
	fn.set_widget_max_size = function ($widget, max_size) {
		$widget = typeof $widget === 'number' ?
				this.$widgets.eq($widget) : $widget;

		if (!$widget.length) {
			return this;
		}

		var wgd = $widget.data('coords').grid;
		wgd.max_size_x = max_size[0];
		wgd.max_size_y = max_size[1];

		return this;
	};


	/**
	 * Append the resize handle into a widget.
	 *
	 * @method add_resize_handle
	 *  representing the widget.
	 * @return {HTMLElement} Returns instance of gridster Class.
	 * @param $w
	 */
	fn.add_resize_handle = function ($w) {
		var $append_to = this.options.resize.handle_append_to ? $(this.options.resize.handle_append_to, $w) : $w;

		if (($append_to.children('span[class~=\'' + this.resize_handle_class + '\']')).length === 0) {
			$(this.resize_handle_tpl).appendTo($append_to);
		}

		return this;
	};


	/**
	 * Change the size of a widget. Width is limited to the current grid width.
	 *
	 * @method resize_widget
	 * @param {HTMLElement} $widget The jQuery wrapped HTMLElement
	 *  representing the widget.
	 * @param {Number} [size_x] The number of columns that will occupy the widget.
	 *  By default <code>size_x</code> is limited to the space available from
	 *  the column where the widget begins, until the last column to the right.
	 * @param {Number} [size_y] The number of rows that will occupy the widget.
	 * @param {Function} [callback] Function executed when the widget is removed.
	 * @return {HTMLElement} Returns $widget.
	 */
	fn.resize_widget = function ($widget, size_x, size_y, callback) {
		var wgd = $widget.coords().grid;

		size_x || (size_x = wgd.size_x);
		size_y || (size_y = wgd.size_y);

		//ensure the grid has the correct number of rows
		if (!this.is_valid_row(wgd.row, size_y)){
			this.add_faux_rows(Math.max(this.calculate_highest_row(wgd.row, size_y) - this.rows, 0));
		}

		//ensure the grid has the correct number of cols
		if (!this.is_valid_col(wgd.col, size_y)) {
			this.add_faux_cols(Math.max(this.calculate_highest_row(wgd.col, size_x) - this.cols, 0));
		}

		var new_grid_data = {
			col: wgd.col,
			row: wgd.row,
			size_x: size_x,
			size_y: size_y
		};

		this.mutate_widget_in_gridmap($widget, wgd, new_grid_data);

		this.set_dom_grid_height();
		this.set_dom_grid_width();

		if (callback) {
			callback.call(this, new_grid_data.size_x, new_grid_data.size_y);
		}

		return $widget;
	};

	/**
	 * Expand the widget. Width is set to the current grid width.
	 *
	 * @method expand_widget
	 * @param {HTMLElement} $widget The jQuery wrapped HTMLElement
	 *  representing the widget.
	 * @param {Number} size_x The number of cols that will occupy the widget.
	 * @param {Number} size_y The number of rows that will occupy the widget.
	 * @param {Number} col The column to resize the widget from.
	 * @param {Function} [callback] Function executed when the widget is expanded.
	 * @return {HTMLElement} Returns $widget.
	 */
	fn.expand_widget = function ($widget, size_x, size_y, col, callback) {
		var wgd = $widget.coords().grid;
		var max_size_x = Math.floor(($(window).width() - this.options.widget_margins[0] * 2) / this.min_widget_width);
		size_x = size_x || Math.min(max_size_x, this.cols);
		size_y || (size_y = wgd.size_y);

		var old_size_y = wgd.size_y;
		$widget.attr('pre_expand_col', wgd.col);
		$widget.attr('pre_expand_sizex', wgd.size_x);
		$widget.attr('pre_expand_sizey', wgd.size_y);
		var new_col = col || 1;

		if (size_y > old_size_y) {
			this.add_faux_rows(Math.max(size_y - old_size_y, 0));
		}

		var new_grid_data = {
			col: new_col,
			row: wgd.row,
			size_x: size_x,
			size_y: size_y
		};

		this.mutate_widget_in_gridmap($widget, wgd, new_grid_data);

		this.set_dom_grid_height();
		this.set_dom_grid_width();

		if (callback) {
			callback.call(this, new_grid_data.size_x, new_grid_data.size_y);
		}

		return $widget;
	};

	/**
	 * Collapse the widget to it's pre-expanded size
	 *
	 * @method collapse_widget
	 * @param {HTMLElement} $widget The jQuery wrapped HTMLElement
	 *  representing the widget.
	 * @param {Function} [callback] Function executed when the widget is collapsed.
	 * @return {HTMLElement} Returns $widget.
	 */
	fn.collapse_widget = function ($widget, callback) {
		var wgd = $widget.coords().grid;
		var size_x = parseInt($widget.attr('pre_expand_sizex'));
		var size_y = parseInt($widget.attr('pre_expand_sizey'));

		var new_col = parseInt($widget.attr('pre_expand_col'));

		var new_grid_data = {
			col: new_col,
			row: wgd.row,
			size_x: size_x,
			size_y: size_y
		};

		this.mutate_widget_in_gridmap($widget, wgd, new_grid_data);

		this.set_dom_grid_height();
		this.set_dom_grid_width();

		if (callback) {
			callback.call(this, new_grid_data.size_x, new_grid_data.size_y);
		}

		return $widget;
	};

	/**
	 * Fit the size of a widget to its content (best guess)
	 *
	 * @method fit_to_content
	 * @param $widget  {HTMLElement} $widget The jQuery wrapped HTMLElement
	 * @param max_cols {Number} max number of columns a widget can take up
	 * @param max_rows {Number} max number of rows a widget can take up
	 * @param {Function} [callback] Function executed when the widget is fit to content.
	 * @return {HTMLElement} Returns $widget.
	 */
	fn.fit_to_content = function ($widget, max_cols, max_rows, callback) {
		var wgd = $widget.coords().grid;
		var width = this.$wrapper.width();
		var height = this.$wrapper.height();
		var col_size = this.options.widget_base_dimensions[0] + (2 * this.options.widget_margins[0]);
		var row_size = this.options.widget_base_dimensions[1] + (2 * this.options.widget_margins[1]);
		var best_cols = Math.ceil((width + (2 * this.options.widget_margins[0])) / col_size);
		var best_rows = Math.ceil((height + (2 * this.options.widget_margins[1])) / row_size);

		var new_grid_data = {
			col: wgd.col,
			row: wgd.row,
			size_x: Math.min(max_cols, best_cols),
			size_y: Math.min(max_rows, best_rows)
		};

		this.mutate_widget_in_gridmap($widget, wgd, new_grid_data);

		this.set_dom_grid_height();
		this.set_dom_grid_width();

		if (callback) {
			callback.call(this, new_grid_data.size_x, new_grid_data.size_y);
		}

		return $widget;
	};


	/**
	 * Centers widgets in grid
	 *
	 * @method center_widgets
	 */
	fn.center_widgets = debounce(function () {
		var wrapper_width = this.$wrapper.width();
		var col_size;
		if (this.is_responsive()) {
			col_size = this.get_responsive_col_width();
		} else {
			col_size = this.options.widget_base_dimensions[0] + (2 * this.options.widget_margins[0]);
		}
		var col_count = Math.floor(Math.max(Math.floor(wrapper_width / col_size), this.min_col_count) / 2) * 2;

		this.options.min_cols = col_count;
		this.options.max_cols = col_count;
		this.options.extra_cols = 0;
		this.set_dom_grid_width(col_count);
		this.cols = col_count;

		var col_dif = (col_count - this.prev_col_count) / 2;

		if (col_dif < 0) {
			if (this.get_min_col() > col_dif * -1) {
				this.shift_cols(col_dif);
			} else {
				this.resize_widget_dimensions(this.options);
			}

			setTimeout($.proxy(function () {
				this.resize_widget_dimensions(this.options);
			}, this), 0);

		} else if (col_dif > 0) {
			this.resize_widget_dimensions(this.options);

			setTimeout($.proxy(function () {
				this.shift_cols(col_dif);
			}, this), 0);

		} else {
			this.resize_widget_dimensions(this.options);

			setTimeout($.proxy(function () {
				this.resize_widget_dimensions(this.options);
			}, this), 0);

		}

		this.prev_col_count = col_count;
		return this;
	}, 200);


	fn.get_min_col = function () {
		return Math.min.apply(Math, this.$widgets.map($.proxy(function (key, widget) {
			return this.get_cells_occupied($(widget).coords().grid).cols;
		}, this)).get());
	};


	fn.shift_cols = function (col_dif) {
		var widgets_coords = this.$widgets.map($.proxy(function (i, widget) {
			var $w = $(widget);
			return this.dom_to_coords($w);
		}, this));
		widgets_coords = Gridster.sort_by_row_and_col_asc(widgets_coords);

		widgets_coords.each($.proxy(function (i, widget) {
			var $widget = $(widget.el);
			var wgd = $widget.coords().grid;
			var col = parseInt($widget.attr('data-col'));

			var new_grid_data = {
				col: Math.max(Math.round(col + col_dif), 1),
				row: wgd.row,
				size_x: wgd.size_x,
				size_y: wgd.size_y
			};
			setTimeout($.proxy(function () {
				this.mutate_widget_in_gridmap($widget, wgd, new_grid_data);
			}, this), 0);
		}, this));
	};


	/**
	 * Change the dimensions of widgets.
	 *
	 * @method resize_widget_dimensions
	 * @param {Object} [options] An Object with all options you want to
	 *        overwrite:
	 *    @param {Array} [options.widget_margins] Margin between widgets.
	 *     The first index for the horizontal margin (left, right) and
	 *     the second for the vertical margin (top, bottom).
	 *    @param {Array} [options.widget_base_dimensions] Base widget dimensions
	 *     in pixels. The first index for the width and the second for the
	 *     height.
	 * @return {Class} Returns the instance of the Gridster Class.
	 */
	fn.resize_widget_dimensions = function (options) {
		if (options.widget_margins) {
			this.options.widget_margins = options.widget_margins;
		}

		if (options.widget_base_dimensions) {
			this.options.widget_base_dimensions = options.widget_base_dimensions;
		}

		this.min_widget_width = (this.options.widget_margins[0] * 2) + this.options.widget_base_dimensions[0];
		this.min_widget_height = (this.options.widget_margins[1] * 2) + this.options.widget_base_dimensions[1];

		this.$widgets.each($.proxy(function (i, widget) {
			var $widget = $(widget);
			this.resize_widget($widget);
		}, this));

		this.generate_grid_and_stylesheet();
		this.get_widgets_from_DOM();
		this.set_dom_grid_height();

		return this;
	};


	/**
	 * Mutate widget dimensions and position in the grid map.
	 *
	 * @method mutate_widget_in_gridmap
	 * @param {HTMLElement} $widget The jQuery wrapped HTMLElement
	 *  representing the widget to mutate.
	 * @param {Object} wgd Current widget grid data (col, row, size_x, size_y).
	 * @param {Object} new_wgd New widget grid data.
	 * @return {HTMLElement} Returns instance of gridster Class.
	 */
	fn.mutate_widget_in_gridmap = function ($widget, wgd, new_wgd) {
		var old_size_y = wgd.size_y;

		var old_cells_occupied = this.get_cells_occupied(wgd);
		var new_cells_occupied = this.get_cells_occupied(new_wgd);

		//find the cells that this widget currently occupies
		var empty_cols = [];
		$.each(old_cells_occupied.cols, function (i, col) {
			if ($.inArray(col, new_cells_occupied.cols) === -1) {
				empty_cols.push(col);
			}
		});

		//find the cells that this widget will occupy
		var occupied_cols = [];
		$.each(new_cells_occupied.cols, function (i, col) {
			if ($.inArray(col, old_cells_occupied.cols) === -1) {
				occupied_cols.push(col);
			}
		});

		//find the rows that it currently occupies
		var empty_rows = [];
		$.each(old_cells_occupied.rows, function (i, row) {
			if ($.inArray(row, new_cells_occupied.rows) === -1) {
				empty_rows.push(row);
			}
		});

		//find the rows that it will occupy
		var occupied_rows = [];
		$.each(new_cells_occupied.rows, function (i, row) {
			if ($.inArray(row, old_cells_occupied.rows) === -1) {
				occupied_rows.push(row);
			}
		});

		this.remove_from_gridmap(wgd);

		if (occupied_cols.length) {
			var cols_to_empty = [
				new_wgd.col, new_wgd.row, new_wgd.size_x, Math.min(old_size_y, new_wgd.size_y), $widget
			];
			this.empty_cells.apply(this, cols_to_empty);
		}

		if (occupied_rows.length) {
			var rows_to_empty = [new_wgd.col, new_wgd.row, new_wgd.size_x, new_wgd.size_y, $widget];
			this.empty_cells.apply(this, rows_to_empty);
		}

		// not the same that wgd = new_wgd;
		wgd.col = new_wgd.col;
		wgd.row = new_wgd.row;
		wgd.size_x = new_wgd.size_x;
		wgd.size_y = new_wgd.size_y;

		this.add_to_gridmap(new_wgd, $widget);

		$widget.removeClass('player-revert');

		this.update_widget_dimensions($widget, new_wgd);

		if (empty_cols.length) {
			var cols_to_remove_holes = [
				empty_cols[0], new_wgd.row,
				empty_cols[empty_cols.length - 1] - empty_cols[0] + 1,
				Math.min(old_size_y, new_wgd.size_y),
				$widget
			];

			this.remove_empty_cells.apply(this, cols_to_remove_holes);
		}

		if (empty_rows.length) {
			var rows_to_remove_holes = [
				new_wgd.col, new_wgd.row, new_wgd.size_x, new_wgd.size_y, $widget
			];
			this.remove_empty_cells.apply(this, rows_to_remove_holes);
		}

		this.move_widget_up($widget);

		return this;
	};


	/**
	 * Move down widgets in cells represented by the arguments col, row, size_x,
	 * size_y
	 *
	 * @method empty_cells
	 * @param {Number} col The column where the group of cells begin.
	 * @param {Number} row The row where the group of cells begin.
	 * @param {Number} size_x The number of columns that the group of cells
	 * occupy.
	 * @param {Number} size_y The number of rows that the group of cells
	 * occupy.
	 * @param {HTMLElement} [$exclude] Exclude widgets from being moved.
	 * @return {Class} Returns the instance of the Gridster Class.
	 */
	fn.empty_cells = function (col, row, size_x, size_y, $exclude) {
		var $nexts = this.widgets_below({
			col: col,
			row: row - size_y,
			size_x: size_x,
			size_y: size_y
		});

		$nexts.not($exclude).each($.proxy(function (i, w) {
			var $w = $(w),
					wgd = $w.coords().grid;
			/*jshint -W018 */
			if (!(wgd.row <= (row + size_y - 1))) {
				return;
			}
			/*jshint +W018 */
			var diff = (row + size_y) - wgd.row;
			this.move_widget_down($w, diff);
		}, this));

		this.set_dom_grid_height();

		return this;
	};


	/**
	 * Move up widgets below cells represented by the arguments col, row, size_x,
	 * size_y.
	 *
	 * @method remove_empty_cells
	 * @param {Number} col The column where the group of cells begin.
	 * @param {Number} row The row where the group of cells begin.
	 * @param {Number} size_x The number of columns that the group of cells
	 * occupy.
	 * @param {Number} size_y The number of rows that the group of cells
	 * occupy.
	 * @param {HTMLElement} exclude Exclude widgets from being moved.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.remove_empty_cells = function (col, row, size_x, size_y, exclude) {
		var $nexts = this.widgets_below({
			col: col,
			row: row,
			size_x: size_x,
			size_y: size_y
		});

		$nexts.not(exclude).each($.proxy(function(i, widget) {
			this.move_widget_up( $(widget), size_y );
		}, this));
		
		this.set_dom_grid_height();

		return this;
	};


	/**
	 * Get the most left column below to add a new widget.
	 *
	 * @method next_position
	 * @param {Number} size_x The nº of rows the widget occupies horizontally.
	 * @param {Number} size_y The nº of columns the widget occupies vertically.
	 * @return {Object} Returns a grid coords object representing the future
	 *  widget coords.
	 */
	fn.next_position = function (size_x, size_y) {
		size_x || (size_x = 1);
		size_y || (size_y = 1);
		var ga = this.gridmap;
		var cols_l = ga.length;
		var valid_pos = [];
		var rows_l;

		for (var c = 1; c < cols_l; c++) {
			rows_l = ga[c].length;
			for (var r = 1; r <= rows_l; r++) {
				var can_move_to = this.can_move_to({
					size_x: size_x,
					size_y: size_y
				}, c, r);

				if (can_move_to) {
					valid_pos.push({
						col: c,
						row: r,
						size_y: size_y,
						size_x: size_x
					});
				}
			}
		}

		if (valid_pos.length) {
			return Gridster.sort_by_row_and_col_asc(valid_pos)[0];
		}
		return false;
	};

	fn.remove_by_grid = function (col, row) {
		var $w = this.is_widget(col, row);
		if ($w) {
			this.remove_widget($w);
		}
	};


	/**
	 * Remove a widget from the grid.
	 *
	 * @method remove_widget
	 * @param {HTMLElement} el The jQuery wrapped HTMLElement you want to remove.
	 * @param {Boolean|Function} [silent] If true, widgets below the removed one
	 * will not move up. If a Function is passed it will be used as callback.
	 * @param {Function} [callback] Function executed after the widget is removed.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.remove_widget = function (el, silent, callback) {
		var $el = el instanceof $ ? el : $(el);
		if ($el.length === 0) {
			//there is nothing to remove, so we can't remove it
			return this;
		}
		var wgd = $el.coords().grid;
		if (wgd === undefined) {
			//there is no grid, so we can't remove it
			return this;
		}

		// if silent is a function assume it's a callback
		if ($.isFunction(silent)) {
			callback = silent;
			silent = false;
		}

		this.cells_occupied_by_placeholder = {};
		this.$widgets = this.$widgets.not($el);

		var $nexts = this.widgets_below($el);

		this.remove_from_gridmap(wgd);

		this.options.hide_element.call(this, $el, $.proxy(function(){
			$el.remove();

			if (!silent) {
				$nexts.each($.proxy(function (i, widget) {
					this.move_widget_up($(widget), wgd.size_y);
				}, this));
			}

			this.set_dom_grid_height();

			if (callback) {
				callback.call(this, el);
			}
		}, this));

		return this;
	};


	/**
	 * Remove all widgets from the grid.
	 *
	 * @method remove_all_widgets
	 * @param {Function} callback Function executed for each widget removed.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.remove_all_widgets = function (callback) {
		this.$widgets.each($.proxy(function (i, el) {
			this.remove_widget(el, true, callback);
		}, this));

		return this;
	};


	/**
	 * Returns a serialized array of the widgets in the grid.
	 *
	 * @method serialize
	 * @param {HTMLElement} [$widgets] The collection of jQuery wrapped
	 *  HTMLElements you want to serialize. If no argument is passed all widgets
	 *  will be serialized.
	 * @return {Array} Returns an Array of Objects with the data specified in
	 *  the serialize_params option.
	 */
	fn.serialize = function ($widgets) {
		$widgets || ($widgets = this.$widgets);
		var result = [];
		$widgets.each($.proxy(function (i, widget) {
			var $w = $(widget);
			if (typeof($w.coords().grid) !== 'undefined') {
				result.push(this.options.serialize_params($w, $w.coords().grid));
			}
		}, this));
		return result;
	};

	/**
	 * Returns a serialized array of the widgets that have changed their
	 *  position.
	 *
	 * @method serialize_changed
	 * @return {Array} Returns an Array of Objects with the data specified in
	 *  the serialize_params option.
	 */
	fn.serialize_changed = function () {
		return this.serialize(this.$changed);
	};


	/**
	 * Convert widgets from DOM elements to "widget grid data" Objects.
	 *
	 * @method dom_to_coords
	 * @param {HTMLElement} $widget The widget to be converted.
	 */
	fn.dom_to_coords = function ($widget) {
		return {
			'col': parseInt($widget.attr('data-col'), 10),
			'row': parseInt($widget.attr('data-row'), 10),
			'size_x': parseInt($widget.attr('data-sizex'), 10) || 1,
			'size_y': parseInt($widget.attr('data-sizey'), 10) || 1,
			'max_size_x': parseInt($widget.attr('data-max-sizex'), 10) || false,
			'max_size_y': parseInt($widget.attr('data-max-sizey'), 10) || false,
			'min_size_x': parseInt($widget.attr('data-min-sizex'), 10) || false,
			'min_size_y': parseInt($widget.attr('data-min-sizey'), 10) || false,
			'el': $widget
		};
	};

	/**
	 * Creates the grid coords object representing the widget an add it to the
	 * mapped array of positions.
	 *
	 * @method register_widget
	 * @param {HTMLElement|Object} $el jQuery wrapped HTMLElement representing
	 *  the widget, or an "widget grid data" Object with (col, row, el ...).
	 * @return {Boolean} Returns true if the widget final position is different
	 *  than the original.
	 */
	fn.register_widget = function ($el) {
		var isDOM = $el instanceof $;
		var wgd = isDOM ? this.dom_to_coords($el) : $el;
		var posChanged = false;
		isDOM || ($el = wgd.el);

		var empty_upper_row = this.can_go_widget_up(wgd);
		if (this.options.shift_widgets_up && empty_upper_row) {
			wgd.row = empty_upper_row;
			$el.attr('data-row', empty_upper_row);
			this.$el.trigger('gridster:positionchanged', [wgd]);
			posChanged = true;
		}

		if (this.options.avoid_overlapped_widgets && !this.can_move_to(
						{size_x: wgd.size_x, size_y: wgd.size_y}, wgd.col, wgd.row)
		) {
			$.extend(wgd, this.next_position(wgd.size_x, wgd.size_y));
			$el.attr({
				'data-col': wgd.col,
				'data-row': wgd.row,
				'data-sizex': wgd.size_x,
				'data-sizey': wgd.size_y
			});
			posChanged = true;
		}

		// attach Coord object to player data-coord attribute
		$el.data('coords', $el.coords());
		// Extend Coord object with grid position info
		$el.data('coords').grid = wgd;

		this.add_to_gridmap(wgd, $el);
		this.update_widget_dimensions($el, wgd);

		this.options.resize.enabled && this.add_resize_handle($el);

		return posChanged;
	};


	/**
	 * Update in the mapped array of positions the value of cells represented by
	 * the grid coords object passed in the `grid_data` param.
	 *
	 * @param {Object} grid_data The grid coords object representing the cells
	 *  to update in the mapped array.
	 * @param {HTMLElement|Boolean} value Pass `false` or the jQuery wrapped
	 *  HTMLElement, depends if you want to delete an existing position or add
	 *  a new one.
	 * @method update_widget_position
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.update_widget_position = function (grid_data, value) {
		this.for_each_cell_occupied(grid_data, function (col, row) {
			if (!this.gridmap[col]) {
				return this;
			}
			this.gridmap[col][row] = value;
		});
		return this;
	};


	/**
	 * Update the width and height for a widgets coordinate data.
	 *
	 * @param {HTMLElement} $widget The widget to update.
	 * @param wgd {Object} wgd Current widget grid data (col, row, size_x, size_y).
	 * @method update_widget_dimensions
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.update_widget_dimensions = function ($widget, wgd) {

		var width = (wgd.size_x * (this.is_responsive() ? this.get_responsive_col_width() : this.options.widget_base_dimensions[0]) +
		((wgd.size_x - 1) * this.options.widget_margins[0]));

		var height = (wgd.size_y * this.options.widget_base_dimensions[1] +
		((wgd.size_y - 1) * this.options.widget_margins[1]));

		$widget.data('coords').update({
			width: width,
			height: height
		});

		$widget.attr({
			'data-col': wgd.col,
			'data-row': wgd.row,
			'data-sizex': wgd.size_x,
			'data-sizey': wgd.size_y
		});

		return this;
	};


	/**
	 * Update dimensions for all widgets in the grid.
	 *
	 * @method update_widgets_dimensions
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.update_widgets_dimensions = function () {
		$.each(this.$widgets, $.proxy(function (idx, widget) {
			var wgd = $(widget).coords().grid;
			if (typeof (wgd) !== 'object') {
				return;
			}
			this.update_widget_dimensions($(widget), wgd);
		}, this));
		return this;
	};


	/**
	 * Remove a widget from the mapped array of positions.
	 *
	 * @method remove_from_gridmap
	 * @param {Object} grid_data The grid coords object representing the cells
	 *  to update in the mapped array.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.remove_from_gridmap = function (grid_data) {
		return this.update_widget_position(grid_data, false);
	};


	/**
	 * Add a widget to the mapped array of positions.
	 *
	 * @method add_to_gridmap
	 * @param {Object} grid_data The grid coords object representing the cells
	 *  to update in the mapped array.
	 * @param {HTMLElement|Boolean} [value] The value to set in the specified
	 *  position .
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.add_to_gridmap = function (grid_data, value) {
		this.update_widget_position(grid_data, value || grid_data.el);
	};


	/**
	 * Make widgets draggable.
	 *
	 * @uses Draggable
	 * @method draggable
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.draggable = function () {
		var self = this;
		var draggable_options = $.extend(true, {}, this.options.draggable, {
			offset_left: this.options.widget_margins[0],
			offset_top: this.options.widget_margins[1],
			container_width: (this.cols * this.min_widget_width) + ((this.cols + 1) * this.options.widget_margins[0]),
			limit: true,
			start: function (event, ui) {
				self.$widgets.filter('.player-revert')
						.removeClass('player-revert');

				self.$player = $(this);
				self.$helper = $(ui.$helper);

				self.helper = !self.$helper.is(self.$player);

				self.on_start_drag.call(self, event, ui);
				self.$el.trigger('gridster:dragstart');
			},
			stop: function (event, ui) {
				self.on_stop_drag.call(self, event, ui);
				self.$el.trigger('gridster:dragstop');
			},
			drag: throttle(function (event, ui) {
				self.on_drag.call(self, event, ui);
				self.$el.trigger('gridster:drag');
			}, 60)
		});

		//this.drag_api = this.$el.gridDraggable(draggable_options);
		this.drag_api = this.$el.dragg(draggable_options).data('drag');
	};


	/**
	 * Bind resize events to get resize working.
	 *
	 * @method resizable
	 * @return {Gridster} Returns instance of gridster Class.
	 */
	fn.resizable = function () {
		this.resize_api = this.$el.gridDraggable({
			items: '.' + this.options.resize.handle_class,
			offset_left: this.options.widget_margins[0],
			container_width: this.container_width,
			move_element: false,
			resize: true,
			limit: this.options.max_cols !== Infinity,
			scroll_container: this.options.scroll_container,
			start: $.proxy(this.on_start_resize, this),
			stop: $.proxy(function (event, ui) {
				delay($.proxy(function () {
					this.on_stop_resize(event, ui);
				}, this), 120);
			}, this),
			drag: throttle($.proxy(this.on_resize, this), 60)
		});

		return this;
	};


	/**
	 * Setup things required for resizing. Like build templates for drag handles.
	 *
	 * @method setup_resize
	 * @return {Gridster} Returns instance of gridster Class.
	 */
	fn.setup_resize = function () {
		this.resize_handle_class = this.options.resize.handle_class;
		var axes = this.options.resize.axes;
		var handle_tpl = '<span class="' + this.resize_handle_class + ' ' +
				this.resize_handle_class + '-{type}" />';

		this.resize_handle_tpl = $.map(axes, function (type) {
			return handle_tpl.replace('{type}', type);
		}).join('');

		if ($.isArray(this.options.draggable.ignore_dragging)) {
			this.options.draggable.ignore_dragging.push(
					'.' + this.resize_handle_class);
		}


		return this;
	};


	/**
	 * This function is executed when the player begins to be dragged.
	 *
	 * @method on_start_drag
	 * @param {Event} event The original browser event
	 * @param {Object} ui A prepared ui object with useful drag-related data
	 */
	fn.on_start_drag = function (event, ui) {
		this.$helper.add(this.$player).add(this.$wrapper).addClass('dragging');

		this.highest_col = this.get_highest_occupied_cell().col;

		this.$player.addClass('player');
		this.player_grid_data = this.$player.coords().grid;
		this.placeholder_grid_data = $.extend({}, this.player_grid_data);

		this.set_dom_grid_height(this.$el.height() +
		(this.player_grid_data.size_y * this.min_widget_height));

		this.set_dom_grid_width(this.cols);

		var pgd_sizex = this.player_grid_data.size_x;
		var cols_diff = this.cols - this.highest_col;

		if (this.options.max_cols === Infinity && cols_diff <= pgd_sizex) {
			this.add_faux_cols(Math.min(pgd_sizex - cols_diff, 1));
		}

		var colliders = this.faux_grid;
		var coords = this.$player.data('coords').coords;

		this.cells_occupied_by_player = this.get_cells_occupied(this.player_grid_data);
		this.cells_occupied_by_placeholder = this.get_cells_occupied(this.placeholder_grid_data);

		this.last_cols = [];
		this.last_rows = [];

		// see jquery.collision.js
		this.collision_api = this.$helper.collision(colliders, this.options.collision);

		this.$preview_holder = $('<' + this.$player.get(0).tagName + ' />', {
			'class': 'preview-holder',
			'data-row': this.$player.attr('data-row'),
			'data-col': this.$player.attr('data-col'),
			css: {
				width: coords.width,
				height: coords.height
			}
		}).appendTo(this.$el);

		if (this.options.draggable.start) {
			this.options.draggable.start.call(this, event, ui);
		}
	};


	/**
	 * This function is executed when the player is being dragged.
	 *
	 * @method on_drag
	 * @param {Event} event The original browser event
	 * @param {Object} ui A prepared ui object with useful drag-related data
	 */
	fn.on_drag = function (event, ui) {
		//break if dragstop has been fired
		if (this.$player === null) {
			return false;
		}
		
		var margin_sides = this.options.widget_margins[0];

		var placeholder_column = this.$preview_holder.attr('data-col');

		var abs_offset = {
			left: ui.position.left + this.baseX - (margin_sides * placeholder_column),
			top: ui.position.top + this.baseY
		};

		// auto grow cols
		if (this.options.max_cols === Infinity) {
			var prcol = this.placeholder_grid_data.col +
					this.placeholder_grid_data.size_x - 1;

			// "- 1" due to adding at least 1 column in on_start_drag
			if (prcol >= this.cols - 1 && this.options.max_cols >= this.cols + 1) {
				this.add_faux_cols(1);
				this.set_dom_grid_width(this.cols + 1);
				this.drag_api.set_limits((this.cols * this.min_widget_width) + ((this.cols + 1) * this.options.widget_margins[0]));
			}

			this.collision_api.set_colliders(this.faux_grid);
		}

		this.colliders_data = this.collision_api.get_closest_colliders(abs_offset);

		this.on_overlapped_column_change(this.on_start_overlapping_column, this.on_stop_overlapping_column);

		this.on_overlapped_row_change(this.on_start_overlapping_row, this.on_stop_overlapping_row);

		if (this.helper && this.$player) {
			this.$player.css({
				'left': ui.position.left,
				'top': ui.position.top
			});
		}

		if (this.options.draggable.drag) {
			this.options.draggable.drag.call(this, event, ui);
		}
	};


	/**
	 * This function is executed when the player stops being dragged.
	 *
	 * @method on_stop_drag
	 * @param {Event} event The original browser event
	 * @param {Object} ui A prepared ui object with useful drag-related data
	 */
	fn.on_stop_drag = function (event, ui) {
		this.$helper.add(this.$player).add(this.$wrapper)
				.removeClass('dragging');
				
		var margin_sides = this.options.widget_margins[0];

		var placeholder_column = this.$preview_holder.attr('data-col');

		ui.position.left = ui.position.left + this.baseX - (margin_sides * placeholder_column);
		ui.position.top = ui.position.top + this.baseY;
		this.colliders_data = this.collision_api.get_closest_colliders(
				ui.position);

		this.on_overlapped_column_change(
				this.on_start_overlapping_column,
				this.on_stop_overlapping_column
		);

		this.on_overlapped_row_change(
				this.on_start_overlapping_row,
				this.on_stop_overlapping_row
		);

		this.$changed = this.$changed.add(this.$player);

		// move the cells down if there is an overlap and we are in static mode
		if (this.options.collision.wait_for_mouseup) {
			this.for_each_cell_occupied(this.placeholder_grid_data, function (tcol, trow) {
				if (this.is_widget(tcol, trow)) {
					this.move_widget_down(this.is_widget(tcol, trow), this.placeholder_grid_data.size_y);
				}
			});
		}

		this.cells_occupied_by_player = this.get_cells_occupied(this.placeholder_grid_data);

		var col = this.placeholder_grid_data.col;
		var row = this.placeholder_grid_data.row;

		this.set_cells_player_occupies(col, row);
		this.$player.coords().grid.row = row;
		this.$player.coords().grid.col = col;

		if (this.options.draggable.stop) {
			this.options.draggable.stop.call(this, event, ui);
		}

		this.$player.addClass('player-revert').removeClass('player')
				.attr({
					'data-col': col,
					'data-row': row
				}).css({
					'left': '',
					'top': ''
				});

		this.$preview_holder.remove();

		this.$player = null;
		this.$helper = null;
		this.placeholder_grid_data = {};
		this.player_grid_data = {};
		this.cells_occupied_by_placeholder = {};
		this.cells_occupied_by_player = {};
		this.w_queue = {};

		this.set_dom_grid_height();
		this.set_dom_grid_width();

		if (this.options.max_cols === Infinity) {
			this.drag_api.set_limits((this.cols * this.min_widget_width) + ((this.cols + 1) * this.options.widget_margins[0]));
		}
	};


	/**
	 * This function is executed every time a widget starts to be resized.
	 *
	 * @method on_start_resize
	 * @param {Event} event The original browser event
	 * @param {Object} ui A prepared ui object with useful drag-related data
	 */
	fn.on_start_resize = function (event, ui) {
		this.$resized_widget = ui.$player.closest('.gs-w');
		this.resize_coords = this.$resized_widget.coords();
		this.resize_wgd = this.resize_coords.grid;
		this.resize_initial_width = this.resize_coords.coords.width;
		this.resize_initial_height = this.resize_coords.coords.height;
		this.resize_initial_sizex = this.resize_coords.grid.size_x;
		this.resize_initial_sizey = this.resize_coords.grid.size_y;
		this.resize_initial_col = this.resize_coords.grid.col;
		this.resize_last_sizex = this.resize_initial_sizex;
		this.resize_last_sizey = this.resize_initial_sizey;

		this.resize_max_size_x = Math.min(this.resize_wgd.max_size_x || this.options.resize.max_size[0],
				this.options.max_cols - this.resize_initial_col + 1);
		this.resize_max_size_y = this.resize_wgd.max_size_y || this.options.resize.max_size[1];

		this.resize_min_size_x = (this.resize_wgd.min_size_x || this.options.resize.min_size[0] || 1);
		this.resize_min_size_y = (this.resize_wgd.min_size_y || this.options.resize.min_size[1] || 1);

		this.resize_initial_last_col = this.get_highest_occupied_cell().col;

		this.set_dom_grid_width(this.cols);

		this.resize_dir = {
			right: ui.$player.is('.' + this.resize_handle_class + '-x'),
			bottom: ui.$player.is('.' + this.resize_handle_class + '-y')
		};

		if (!this.is_responsive()) {
			this.$resized_widget.css({
				'min-width': this.options.widget_base_dimensions[0],
				'min-height': this.options.widget_base_dimensions[1]
			});
		}

		var nodeName = this.$resized_widget.get(0).tagName;
		this.$resize_preview_holder = $('<' + nodeName + ' />', {
			'class': 'preview-holder resize-preview-holder',
			'data-row': this.$resized_widget.attr('data-row'),
			'data-col': this.$resized_widget.attr('data-col'),
			'css': {
				'width': this.resize_initial_width,
				'height': this.resize_initial_height
			}
		}).appendTo(this.$el);

		this.$resized_widget.addClass('resizing');

		if (this.options.resize.start) {
			this.options.resize.start.call(this, event, ui, this.$resized_widget);
		}

		this.$el.trigger('gridster:resizestart');
	};


	/**
	 * This function is executed every time a widget stops being resized.
	 *
	 * @method on_stop_resize
	 * @param {Event} event The original browser event
	 * @param {Object} ui A prepared ui object with useful drag-related data
	 */
	fn.on_stop_resize = function (event, ui) {
		this.$resized_widget
				.removeClass('resizing')
				.css({
					'width': '',
					'height': '',
					'min-width': '',
					'min-height': ''
				});

		delay($.proxy(function () {
			this.$resize_preview_holder
					.remove()
					.css({
						'min-width': '',
						'min-height': ''
					});

			if (this.options.resize.stop) {
				this.options.resize.stop.call(this, event, ui, this.$resized_widget);
			}

			this.$el.trigger('gridster:resizestop');
		}, this), 300);

		this.set_dom_grid_width();
		this.set_dom_grid_height();

		if (this.options.max_cols === Infinity) {
			this.drag_api.set_limits(this.cols * this.min_widget_width);
		}
	};


	/**
	 * This function is executed when a widget is being resized.
	 *
	 * @method on_resize
	 * @param {Event} event The original browser event
	 * @param {Object} ui A prepared ui object with useful drag-related data
	 */
	fn.on_resize = function (event, ui) {
		var rel_x = (ui.pointer.diff_left);
		var rel_y = (ui.pointer.diff_top);
		var wbd_x = this.is_responsive() ? this.get_responsive_col_width() : this.options.widget_base_dimensions[0];
		var wbd_y = this.options.widget_base_dimensions[1];
		var margin_x = this.options.widget_margins[0];
		var margin_y = this.options.widget_margins[1];
		var max_size_x = this.resize_max_size_x;
		var min_size_x = this.resize_min_size_x;
		var max_size_y = this.resize_max_size_y;
		var min_size_y = this.resize_min_size_y;
		var autogrow = this.options.max_cols === Infinity;
		var width;

		var inc_units_x = Math.ceil((rel_x / (wbd_x + margin_x * 2)) - 0.2);
		var inc_units_y = Math.ceil((rel_y / (wbd_y + margin_y * 2)) - 0.2);

		var size_x = Math.max(1, this.resize_initial_sizex + inc_units_x);
		var size_y = Math.max(1, this.resize_initial_sizey + inc_units_y);

		// Max number of cols this widget can be in width
		var max_cols = Math.floor((this.container_width / this.min_widget_width) - this.resize_initial_col + 1);

		var limit_width = (max_cols * this.min_widget_width) + ((max_cols - 1) * margin_x);

		size_x = Math.max(Math.min(size_x, max_size_x), min_size_x);
		size_x = Math.min(max_cols, size_x);
		width = (max_size_x * wbd_x) + ((size_x - 1) * margin_x );
		var max_width = Math.min(width, limit_width);
		var min_width = (min_size_x * wbd_x) + ((size_x - 1) * margin_x);

		size_y = Math.max(Math.min(size_y, max_size_y), min_size_y);
		var max_height = (max_size_y * wbd_y) + ((size_y - 1) * margin_y);
		var min_height = (min_size_y * wbd_y) + ((size_y - 1) * margin_y);

		if (this.resize_dir.right) {
			size_y = this.resize_initial_sizey;
		} else if (this.resize_dir.bottom) {
			size_x = this.resize_initial_sizex;
		}

		if (autogrow) {
			var last_widget_col = this.resize_initial_col + size_x - 1;
			if (autogrow && this.resize_initial_last_col <= last_widget_col) {
				this.set_dom_grid_width(Math.max(last_widget_col + 1, this.cols));

				if (this.cols < last_widget_col) {
					this.add_faux_cols(last_widget_col - this.cols);
				}
			}
		}

		var css_props = {};
		!this.resize_dir.bottom && (css_props.width = Math.max(Math.min(
				this.resize_initial_width + rel_x, max_width), min_width));
		!this.resize_dir.right && (css_props.height = Math.max(Math.min(
				this.resize_initial_height + rel_y, max_height), min_height));

		this.$resized_widget.css(css_props);

		if (size_x !== this.resize_last_sizex ||
				size_y !== this.resize_last_sizey) {

			this.resize_widget(this.$resized_widget, size_x, size_y, false);
			this.set_dom_grid_width(this.cols);

			this.$resize_preview_holder.css({
				'width': '',
				'height': ''
			}).attr({
				'data-row': this.$resized_widget.attr('data-row'),
				'data-sizex': size_x,
				'data-sizey': size_y
			});
		}

		if (this.options.resize.resize) {
			this.options.resize.resize.call(this, event, ui, this.$resized_widget);
		}

		this.$el.trigger('gridster:resize');

		this.resize_last_sizex = size_x;
		this.resize_last_sizey = size_y;
	};


	/**
	 * Executes the callbacks passed as arguments when a column begins to be
	 * overlapped or stops being overlapped.
	 *
	 * @param {Function} start_callback Function executed when a new column
	 *  begins to be overlapped. The column is passed as first argument.
	 * @param {Function} stop_callback Function executed when a column stops
	 *  being overlapped. The column is passed as first argument.
	 * @method on_overlapped_column_change
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.on_overlapped_column_change = function (start_callback, stop_callback) {
		if (!this.colliders_data.length) {
			return this;
		}
		var cols = this.get_targeted_columns(
				this.colliders_data[0].el.data.col);

		var last_n_cols = this.last_cols.length;
		var n_cols = cols.length;
		var i;

		for (i = 0; i < n_cols; i++) {
			if ($.inArray(cols[i], this.last_cols) === -1) {
				(start_callback || $.noop).call(this, cols[i]);
			}
		}

		for (i = 0; i < last_n_cols; i++) {
			if ($.inArray(this.last_cols[i], cols) === -1) {
				(stop_callback || $.noop).call(this, this.last_cols[i]);
			}
		}

		this.last_cols = cols;

		return this;
	};


	/**
	 * Executes the callbacks passed as arguments when a row starts to be
	 * overlapped or stops being overlapped.
	 *
	 * @param {Function} start_callback Function executed when a new row begins
	 *  to be overlapped. The row is passed as first argument.
	 * @param {Function} end_callback Function executed when a row stops being
	 *  overlapped. The row is passed as first argument.
	 * @method on_overlapped_row_change
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.on_overlapped_row_change = function (start_callback, end_callback) {
		if (!this.colliders_data.length) {
			return this;
		}
		var rows = this.get_targeted_rows(this.colliders_data[0].el.data.row);
		var last_n_rows = this.last_rows.length;
		var n_rows = rows.length;
		var i;

		for (i = 0; i < n_rows; i++) {
			if ($.inArray(rows[i], this.last_rows) === -1) {
				(start_callback || $.noop).call(this, rows[i]);
			}
		}

		for (i = 0; i < last_n_rows; i++) {
			if ($.inArray(this.last_rows[i], rows) === -1) {
				(end_callback || $.noop).call(this, this.last_rows[i]);
			}
		}

		this.last_rows = rows;
	};


	/**
	 * Sets the current position of the player
	 *
	 * @param {Number} col
	 * @param {Number} row
	 * @param {Boolean} no_player
	 * @method set_player
	 * @return {object}
	 */
	fn.set_player = function (col, row, no_player) {
		var self = this;
		var swap = false;
		if (!no_player) {
			this.empty_cells_player_occupies();
		}
		var cell = !no_player ? self.colliders_data[0].el.data : {col: col};
		var to_col = cell.col;
		var to_row = cell.row || row;

		this.player_grid_data = {
			col: to_col,
			row: to_row,
			size_y: this.player_grid_data.size_y,
			size_x: this.player_grid_data.size_x
		};

		this.cells_occupied_by_player = this.get_cells_occupied(
				this.player_grid_data);

		//Added placeholder for more advanced movement.
		this.cells_occupied_by_placeholder = this.get_cells_occupied(
				this.placeholder_grid_data);

		var $overlapped_widgets = this.get_widgets_overlapped(this.player_grid_data);

		var player_size_y = this.player_grid_data.size_y;
		var player_size_x = this.player_grid_data.size_x;
		var placeholder_cells = this.cells_occupied_by_placeholder;
		var $gr = this;


		//Queue Swaps
		$overlapped_widgets.each($.proxy(function (i, w) {
			var $w = $(w);
			var wgd = $w.coords().grid;
			var outside_col = placeholder_cells.cols[0] + player_size_x - 1;
			var outside_row = placeholder_cells.rows[0] + player_size_y - 1;
			if ($w.hasClass($gr.options.static_class)) {
				//next iteration
				return true;
			}
			if ($gr.options.collision.wait_for_mouseup && $gr.drag_api.is_dragging){
				//skip the swap and just 'move' the place holder
				$gr.placeholder_grid_data.col = to_col;
				$gr.placeholder_grid_data.row = to_row;

				$gr.cells_occupied_by_placeholder = $gr.get_cells_occupied(
						$gr.placeholder_grid_data);

				$gr.$preview_holder.attr({
					'data-row': to_row,
					'data-col': to_col
				});
			} else if (wgd.size_x <= player_size_x && wgd.size_y <= player_size_y) {
				if (!$gr.is_swap_occupied(placeholder_cells.cols[0], wgd.row, wgd.size_x, wgd.size_y) && !$gr.is_player_in(placeholder_cells.cols[0], wgd.row) && !$gr.is_in_queue(placeholder_cells.cols[0], wgd.row, $w)) {
					swap = $gr.queue_widget(placeholder_cells.cols[0], wgd.row, $w);
				}
				else if (!$gr.is_swap_occupied(outside_col, wgd.row, wgd.size_x, wgd.size_y) && !$gr.is_player_in(outside_col, wgd.row) && !$gr.is_in_queue(outside_col, wgd.row, $w)) {
					swap = $gr.queue_widget(outside_col, wgd.row, $w);
				}
				else if (!$gr.is_swap_occupied(wgd.col, placeholder_cells.rows[0], wgd.size_x, wgd.size_y) && !$gr.is_player_in(wgd.col, placeholder_cells.rows[0]) && !$gr.is_in_queue(wgd.col, placeholder_cells.rows[0], $w)) {
					swap = $gr.queue_widget(wgd.col, placeholder_cells.rows[0], $w);
				}
				else if (!$gr.is_swap_occupied(wgd.col, outside_row, wgd.size_x, wgd.size_y) && !$gr.is_player_in(wgd.col, outside_row) && !$gr.is_in_queue(wgd.col, outside_row, $w)) {
					swap = $gr.queue_widget(wgd.col, outside_row, $w);
				}
				else if (!$gr.is_swap_occupied(placeholder_cells.cols[0], placeholder_cells.rows[0], wgd.size_x, wgd.size_y) && !$gr.is_player_in(placeholder_cells.cols[0], placeholder_cells.rows[0]) && !$gr.is_in_queue(placeholder_cells.cols[0], placeholder_cells.rows[0], $w)) {
					swap = $gr.queue_widget(placeholder_cells.cols[0], placeholder_cells.rows[0], $w);
				} else {
					//in one last attempt we check for any other empty spaces
					for (var c = 0; c < player_size_x; c++) {
						for (var r = 0; r < player_size_y; r++) {
							var colc = placeholder_cells.cols[0] + c;
							var rowc = placeholder_cells.rows[0] + r;
							if (!$gr.is_swap_occupied(colc, rowc, wgd.size_x, wgd.size_y) && !$gr.is_player_in(colc, rowc) && !$gr.is_in_queue(colc, rowc, $w)) {
								swap = $gr.queue_widget(colc, rowc, $w);
								c = player_size_x;
								break;
							}
						}
					}

				}
			} else if ($gr.options.shift_larger_widgets_down && !swap) {
				$overlapped_widgets.each($.proxy(function (i, w) {
					var $w = $(w);

					if ($gr.can_go_down($w) && $w.coords().grid.row === $gr.player_grid_data.row) {
						$gr.move_widget_down($w, $gr.player_grid_data.size_y);
						$gr.set_placeholder(to_col, to_row);
					}
				}));
			}

			$gr.clean_up_changed();
		}));

		//Move queued widgets
		if (swap && this.can_placeholder_be_set(to_col, to_row, player_size_x, player_size_y)) {
			for (var key in this.w_queue) {
				var _col = parseInt(key.split('_')[0]);
				var _row = parseInt(key.split('_')[1]);
				if (this.w_queue[key] !== 'full') {
					this.new_move_widget_to(this.w_queue[key], _col, _row);
				}
			}
			this.set_placeholder(to_col, to_row);
		}

		/* if there is not widgets overlapping in the new player position,
		 * update the new placeholder position. */
		if (!$overlapped_widgets.length) {
			if (this.options.shift_widgets_up) {
				var pp = this.can_go_player_up(this.player_grid_data);
				if (pp !== false) {
					to_row = pp;
				}
			}
			if (this.can_placeholder_be_set(to_col, to_row, player_size_x, player_size_y)) {
				this.set_placeholder(to_col, to_row);
			}
		}

		this.w_queue = {};

		return {
			col: to_col,
			row: to_row
		};
	};


	fn.is_swap_occupied = function (col, row, w_size_x, w_size_y) {
		var occupied = false;
		for (var c = 0; c < w_size_x; c++) {
			for (var r = 0; r < w_size_y; r++) {
				var colc = col + c;
				var rowc = row + r;
				var key = colc + '_' + rowc;
				if (this.is_occupied(colc, rowc)) {
					occupied = true;
				} else if (key in this.w_queue) {
					if (this.w_queue[key] === 'full') {
						occupied = true;
						continue;
					}
					var $tw = this.w_queue[key];
					var tgd = $tw.coords().grid;
					//remove queued items if no longer under player.
					if (!this.is_widget_under_player(tgd.col, tgd.row)) {
						delete this.w_queue[key];
					}
				}
				if (rowc > parseInt(this.options.max_rows)) {
					occupied = true;
				}
				if (colc > parseInt(this.options.max_cols)) {
					occupied = true;
				}
				if (this.is_player_in(colc, rowc)) {
					occupied = true;
				}
			}
		}

		return occupied;
	};

	fn.can_placeholder_be_set = function (col, row, player_size_x, player_size_y) {
		var can_set = true;
		for (var c = 0; c < player_size_x; c++) {
			for (var r = 0; r < player_size_y; r++) {
				var colc = col + c;
				var rowc = row + r;
				var $tw = this.is_widget(colc, rowc);
				//if this space is occupied and not queued for move.
				if (rowc > parseInt(this.options.max_rows)) {
					can_set = false;
				}
				if (colc > parseInt(this.options.max_cols)) {
					can_set = false;
				}
				if (this.is_occupied(colc, rowc) && !this.is_widget_queued_and_can_move($tw)) {
					can_set = false;
				}
			}
		}
		return can_set;
	};

	fn.queue_widget = function (col, row, $widget) {
		var $w = $widget;
		var wgd = $w.coords().grid;
		var primary_key = col + '_' + row;
		if (primary_key in this.w_queue) {
			return false;
		}

		this.w_queue[primary_key] = $w;

		for (var c = 0; c < wgd.size_x; c++) {
			for (var r = 0; r < wgd.size_y; r++) {
				var colc = col + c;
				var rowc = row + r;
				var key = colc + '_' + rowc;
				if (key === primary_key) {
					continue;
				}
				this.w_queue[key] = 'full';
			}
		}

		return true;
	};

	fn.is_widget_queued_and_can_move = function ($widget) {
		var queued = false;
		if ($widget === false) {
			return false;
		}

		for (var key in this.w_queue) {
			if (this.w_queue[key] === 'full') {
				continue;
			}
			if (this.w_queue[key].attr('data-col') === $widget.attr('data-col') && this.w_queue[key].attr('data-row') === $widget.attr('data-row')) {
				queued = true;
				//test whole space
				var $w = this.w_queue[key];
				var dcol = parseInt(key.split('_')[0]);
				var drow = parseInt(key.split('_')[1]);
				var wgd = $w.coords().grid;

				for (var c = 0; c < wgd.size_x; c++) {
					for (var r = 0; r < wgd.size_y; r++) {
						var colc = dcol + c;
						var rowc = drow + r;
						if (this.is_player_in(colc, rowc)) {
							queued = false;
						}

					}
				}

			}
		}

		return queued;
	};

	fn.is_in_queue = function (col, row, $widget) {
		var queued = false;
		var key = col + '_' + row;

		if ((key in this.w_queue)) {
			if (this.w_queue[key] === 'full') {
				queued = true;
			} else {
				var $tw = this.w_queue[key];
				var tgd = $tw.coords().grid;
				if (!this.is_widget_under_player(tgd.col, tgd.row)) {
					delete this.w_queue[key];
					queued = false;
				} else if (this.w_queue[key].attr('data-col') === $widget.attr('data-col') && this.w_queue[key].attr('data-row') === $widget.attr('data-row')) {
					delete this.w_queue[key];
					queued = false;
				} else {
					queued = true;
				}
			}
		}

		return queued;
	};


	/**
	 * See which of the widgets in the $widgets param collection can go to
	 * a upper row and which not.
	 *
	 * @method widgets_contraints
	 * @param {jQuery} $widgets A jQuery wrapped collection of
	 * HTMLElements.
	 * @return {object} Returns a literal Object with two keys: `can_go_up` &
	 * `can_not_go_up`. Each contains a set of HTMLElements.
	 */
	fn.widgets_constraints = function ($widgets) {
		var $widgets_can_go_up = $([]);
		var $widgets_can_not_go_up;
		var wgd_can_go_up = [];
		var wgd_can_not_go_up = [];

		$widgets.each($.proxy(function (i, w) {
			var $w = $(w);
			var wgd = $w.coords().grid;
			if (this.can_go_widget_up(wgd)) {
				$widgets_can_go_up = $widgets_can_go_up.add($w);
				wgd_can_go_up.push(wgd);
			} else {
				wgd_can_not_go_up.push(wgd);
			}
		}, this));

		$widgets_can_not_go_up = $widgets.not($widgets_can_go_up);

		return {
			can_go_up: Gridster.sort_by_row_asc(wgd_can_go_up),
			can_not_go_up: Gridster.sort_by_row_desc(wgd_can_not_go_up)
		};
	};


	/**
	 * Sorts an Array of grid coords objects (representing the grid coords of
	 * each widget) in descending way.

	 * Depreciated.
	 *
	 * @method manage_movements
	 * @param {jQuery} $widgets A jQuery collection of HTMLElements
	 *  representing the widgets you want to move.
	 * @param {Number} to_col The column to which we want to move the widgets.
	 * @param {Number} to_row The row to which we want to move the widgets.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.manage_movements = function ($widgets, to_col, to_row) {
		$.each($widgets, $.proxy(function (i, w) {
			var wgd = w;
			var $w = wgd.el;

			var can_go_widget_up = this.can_go_widget_up(wgd);

			if (can_go_widget_up) {
				//target CAN go up
				//so move widget up
				this.move_widget_to($w, can_go_widget_up);
				this.set_placeholder(to_col, can_go_widget_up + wgd.size_y);

			} else {
				//target can't go up
				var can_go_player_up = this.can_go_player_up(
						this.player_grid_data);

				if (!can_go_player_up) {
					// target can't go up
					// player cant't go up
					// so we need to move widget down to a position that dont
					// overlaps player
					var y = (to_row + this.player_grid_data.size_y) - wgd.row;
					if (this.can_go_down($w)) {
						console.log('In Move Down!');
						this.move_widget_down($w, y);
						this.set_placeholder(to_col, to_row);
					}
				}
			}
		}, this));

		return this;
	};

	/**
	 * Determines if there is a widget in the row and col given. Or if the
	 * HTMLElement passed as first argument is the player.
	 *
	 * @method is_player
	 * @param {Number|HTMLElement} col_or_el A jQuery wrapped collection of
	 * HTMLElements.
	 * @param {Number} [row] The column to which we want to move the widgets.
	 * @return {Boolean} Returns true or false.
	 */
	fn.is_player = function (col_or_el, row) {
		if (row && !this.gridmap[col_or_el]) {
			return false;
		}
		var $w = row ? this.gridmap[col_or_el][row] : col_or_el;
		return $w && ($w.is(this.$player) || $w.is(this.$helper));
	};


	/**
	 * Determines if the widget that is being dragged is currently over the row
	 * and col given.
	 *
	 * @method is_player_in
	 * @param {Number} col The column to check.
	 * @param {Number} row The row to check.
	 * @return {Boolean} Returns true or false.
	 */
	fn.is_player_in = function (col, row) {
		var c = this.cells_occupied_by_player || {};
		return $.inArray(col, c.cols) >= 0 && $.inArray(row, c.rows) >= 0;
	};


	/**
	 * Determines if the placeholder is currently over the row and col given.
	 *
	 * @method is_placeholder_in
	 * @param {Number} col The column to check.
	 * @param {Number} row The row to check.
	 * @return {Boolean} Returns true or false.
	 */
	fn.is_placeholder_in = function (col, row) {
		var c = this.cells_occupied_by_placeholder || {};
		return this.is_placeholder_in_col(col) && $.inArray(row, c.rows) >= 0;
	};


	/**
	 * Determines if the placeholder is currently over the column given.
	 *
	 * @method is_placeholder_in_col
	 * @param {Number} col The column to check.
	 * @return {Boolean} Returns true or false.
	 */
	fn.is_placeholder_in_col = function (col) {
		var c = this.cells_occupied_by_placeholder || [];
		return $.inArray(col, c.cols) >= 0;
	};


	/**
	 * Determines if the cell represented by col and row params is empty.
	 *
	 * @method is_empty
	 * @param {Number} col The column to check.
	 * @param {Number} row The row to check.
	 * @return {Boolean} Returns true or false.
	 */
	fn.is_empty = function (col, row) {
		if (typeof this.gridmap[col] !== 'undefined') {
			if (typeof this.gridmap[col][row] !== 'undefined' &&
					this.gridmap[col][row] === false
			) {
				return true;
			}
			return false;
		}
		return true;
	};


	/**
	 * checks the grid to see if the desired column is a valid row in the config
	 * @Param {Number} col number to check
	 * @Param {Number} [size_y] optional number of columns in the offset
	 * @Return {Boolean} true if the desire column exists in the grid.
	 */
	fn.is_valid_col = function (col, size_x) {
		//if the grid is set to autogrow all cols are valid
		if (this.options.max_cols === Infinity) {
			return true;
		}
		return this.cols >= this.calculate_highest_col(col, size_x) ;
	};

	/**
	 * checks the grid to see if the desired row is a valid row in the config
	 * @Param {Number} row number to check
	 * @Param {Number} [size_y] optional number of rows in the offset
	 * @Return {Boolean} true if the desire row exists in the grid.
	 */
	fn.is_valid_row = function (row, size_y){
		return  this.rows >= this.calculate_highest_row(row, size_y);
	};

	/**
	 * extract out the logic to calculate the highest col the widget needs
	 * in the grid in order to fit.  Based on the current row and desired size
	 * @param {Number} col the column number of the current postiton of the widget
	 * @param  {Number} [size_x] veritical size of the widget
	 * @returns {number} highest col needed to contain the widget
	 */
	fn.calculate_highest_col = function (col, size_x) {
		return col + (size_x || 1) - 1;
	};

	/**
	 * extract out the logic to calculate the highest row the widget needs
	 * in the grid in order to fit.  Based on the current row and desired size
	 * @param {Number} row the row number of the current postiton of the widget
	 * @param  {Number} [size_y] horizontal size of the widget
	 * @returns {number} highest row needed to contain the widget
	 */
	fn.calculate_highest_row = function (row, size_y){
		return row + (size_y || 1) - 1;
	};

	/**
	 * Determines if the cell represented by col and row params is occupied.
	 *
	 * @method is_occupied
	 * @param {Number} col The column to check.
	 * @param {Number} row The row to check.
	 * @return {Boolean} Returns true or false.
	 */
	fn.is_occupied = function (col, row) {
		if (!this.gridmap[col]) {
			return false;
		}

		if (this.gridmap[col][row]) {
			return true;
		}
		return false;
	};


	/**
	 * Determines if there is a widget in the cell represented by col/row params.
	 *
	 * @method is_widget
	 * @param {Number} col The column to check.
	 * @param {Number} row The row to check.
	 * @return {Boolean|HTMLElement} Returns false if there is no widget,
	 * else returns the jQuery HTMLElement
	 */
	fn.is_widget = function (col, row) {
		var cell = this.gridmap[col];
		if (!cell) {
			return false;
		}

		cell = cell[row];

		if (cell) {
			return cell;
		}

		return false;
	};

	/**
	 * Determines if widget is supposed to be static.
	 * WARNING: as of 0.6.6 this feature is buggy when
	 * used with resizable widgets, as resizing widgets
	 * above and below a static widgit can cause it to move.
	 * This feature is considered experimental at this time
	 * @method is_static
	 * @param {Number} col The column to check.
	 * @param {Number} row The row to check.
	 * @return {Boolean} Returns true if widget exists and has static class,
	 * else returns false
	 */
	fn.is_static = function (col, row) {
		var cell = this.gridmap[col];
		if (!cell) {
			return false;
		}

		cell = cell[row];

		if (cell) {
			if (cell.hasClass(this.options.static_class)) {
				return true;
			}
		}

		return false;
	};


	/**
	 * Determines if there is a widget in the cell represented by col/row
	 * params and if this is under the widget that is being dragged.
	 *
	 * @method is_widget_under_player
	 * @param {Number} col The column to check.
	 * @param {Number} row The row to check.
	 * @return {Boolean} Returns true or false.
	 */
	fn.is_widget_under_player = function (col, row) {
		if (this.is_widget(col, row)) {
			return this.is_player_in(col, row);
		}
		return false;
	};


	/**
	 * Get widgets overlapping with the player or with the object passed
	 * representing the grid cells.
	 *
	 * @method get_widgets_under_player
	 * @return {HTMLElement} Returns a jQuery collection of HTMLElements
	 */
	fn.get_widgets_under_player = function (cells) {
		cells || (cells = this.cells_occupied_by_player || {cols: [], rows: []});
		var $widgets = $([]);

		$.each(cells.cols, $.proxy(function (i, col) {
			$.each(cells.rows, $.proxy(function (i, row) {
				if (this.is_widget(col, row)) {
					$widgets = $widgets.add(this.gridmap[col][row]);
				}
			}, this));
		}, this));

		return $widgets;
	};


	/**
	 * Put placeholder at the row and column specified.
	 *
	 * @method set_placeholder
	 * @param {Number} col The column to which we want to move the
	 *  placeholder.
	 * @param {Number} row The row to which we want to move the
	 *  placeholder.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.set_placeholder = function (col, row) {
		var phgd = $.extend({}, this.placeholder_grid_data);

		// Prevents widgets go out of the grid
		var right_col = (col + phgd.size_x - 1);
		if (right_col > this.cols) {
			col = col - (right_col - col);
		}

		var moved_down = this.placeholder_grid_data.row < row;
		var changed_column = this.placeholder_grid_data.col !== col;

		this.placeholder_grid_data.col = col;
		this.placeholder_grid_data.row = row;

		this.cells_occupied_by_placeholder = this.get_cells_occupied(
				this.placeholder_grid_data);

		this.$preview_holder.attr({
			'data-row': row,
			'data-col': col
		});

		if (this.options.shift_player_up) {
			if (moved_down || changed_column) {

				var $nexts = this.widgets_below({
					col: phgd.col,
					row: phgd.row,
					size_y: phgd.size_y,
					size_x: phgd.size_x
				});

				$nexts.each($.proxy(function (i, widget) {
					//Make sure widget is at it's topmost position
					var $w = $(widget);
					var wgd = $w.coords().grid;

					var can_go_widget_up = this.can_go_widget_up(wgd);

					if (can_go_widget_up) {
						this.move_widget_to($w, can_go_widget_up);
					}

				}, this));
			}

			var $widgets_under_ph = this.get_widgets_under_player(
					this.cells_occupied_by_placeholder);

			if ($widgets_under_ph.length) {
				$widgets_under_ph.each($.proxy(function (i, widget) {
					var $w = $(widget);
					this.move_widget_down(
							$w, row + phgd.size_y - $w.data('coords').grid.row);
				}, this));
			}
		}

	};


	/**
	 * Determines whether the player can move to a position above.
	 *
	 * @method can_go_player_up
	 * @param {Object} widget_grid_data The actual grid coords object of the
	 *  player.
	 * @return {Number|Boolean} If the player can be moved to an upper row
	 *  returns the row number, else returns false.
	 */
	fn.can_go_player_up = function (widget_grid_data) {
		var p_bottom_row = widget_grid_data.row + widget_grid_data.size_y - 1;
		var result = true;
		var upper_rows = [];
		var min_row = 10000;
		var $widgets_under_player = this.get_widgets_under_player();

		/* generate an array with columns as index and array with upper rows
		 * empty as value */
		this.for_each_column_occupied(widget_grid_data, function (tcol) {
			var grid_col = this.gridmap[tcol];
			var r = p_bottom_row + 1;
			upper_rows[tcol] = [];

			while (--r > 0) {
				if (this.is_empty(tcol, r) || this.is_player(tcol, r) ||
						this.is_widget(tcol, r) &&
						grid_col[r].is($widgets_under_player)
				) {
					upper_rows[tcol].push(r);
					min_row = r < min_row ? r : min_row;
				} else {
					break;
				}
			}

			if (upper_rows[tcol].length === 0) {
				result = false;
				return true; //break
			}

			upper_rows[tcol].sort(function (a, b) {
				return a - b;
			});
		});

		if (!result) {
			return false;
		}

		return this.get_valid_rows(widget_grid_data, upper_rows, min_row);
	};


	/**
	 * Determines whether a widget can move to a position above.
	 *
	 * @method can_go_widget_up
	 * @param {Object} widget_grid_data The actual grid coords object of the
	 *  widget we want to check.
	 * @return {Number|Boolean} If the widget can be moved to an upper row
	 *  returns the row number, else returns false.
	 */
	fn.can_go_widget_up = function (widget_grid_data) {
		var p_bottom_row = widget_grid_data.row + widget_grid_data.size_y - 1;
		var result = true;
		var upper_rows = [];
		var min_row = 10000;

		/* generate an array with columns as index and array with topmost rows
		 * empty as value */
		this.for_each_column_occupied(widget_grid_data, function (tcol) {
			var grid_col = this.gridmap[tcol];
			upper_rows[tcol] = [];

			var r = p_bottom_row + 1;
			// iterate over each row
			while (--r > 0) {
				if (this.is_widget(tcol, r) && !this.is_player_in(tcol, r)) {
					if (!grid_col[r].is(widget_grid_data.el)) {
						break;
					}
				}

				if (!this.is_player(tcol, r) && !this.is_placeholder_in(tcol, r) && !this.is_player_in(tcol, r)) {
					upper_rows[tcol].push(r);
				}

				if (r < min_row) {
					min_row = r;
				}
			}

			if (upper_rows[tcol].length === 0) {
				result = false;
				return true; //break
			}

			upper_rows[tcol].sort(function (a, b) {
				return a - b;
			});
		});

		if (!result) {
			return false;
		}

		return this.get_valid_rows(widget_grid_data, upper_rows, min_row);
	};


	/**
	 * Search a valid row for the widget represented by `widget_grid_data' in
	 * the `upper_rows` array. Iteration starts from row specified in `min_row`.
	 *
	 * @method get_valid_rows
	 * @param {Object} widget_grid_data The actual grid coords object of the
	 *  player.
	 * @param {Array} upper_rows An array with columns as index and arrays
	 *  of valid rows as values.
	 * @param {Number} min_row The upper row from which the iteration will start.
	 * @return {Number|Boolean} Returns the upper row valid from the `upper_rows`
	 *  for the widget in question.
	 */
	fn.get_valid_rows = function (widget_grid_data, upper_rows, min_row) {
		var p_top_row = widget_grid_data.row;
		var p_bottom_row = widget_grid_data.row + widget_grid_data.size_y - 1;
		var size_y = widget_grid_data.size_y;
		var r = min_row - 1;
		var valid_rows = [];

		while (++r <= p_bottom_row) {
			var common = true;
			/*jshint -W083 */
			$.each(upper_rows, function (col, rows) {
				if ($.isArray(rows) && $.inArray(r, rows) === -1) {
					common = false;
				}
			});
			/*jshint +W083 */
			if (common === true) {
				valid_rows.push(r);
				if (valid_rows.length === size_y) {
					break;
				}
			}
		}

		var new_row = false;
		if (size_y === 1) {
			if (valid_rows[0] !== p_top_row) {
				new_row = valid_rows[0] || false;
			}
		} else {
			if (valid_rows[0] !== p_top_row) {
				new_row = this.get_consecutive_numbers_index(
						valid_rows, size_y);
			}
		}

		return new_row;
	};


	fn.get_consecutive_numbers_index = function (arr, size_y) {
		var max = arr.length;
		var result = [];
		var first = true;
		var prev = -1; // or null?

		for (var i = 0; i < max; i++) {
			if (first || arr[i] === prev + 1) {
				result.push(i);
				if (result.length === size_y) {
					break;
				}
				first = false;
			} else {
				result = [];
				first = true;
			}

			prev = arr[i];
		}

		return result.length >= size_y ? arr[result[0]] : false;
	};


	/**
	 * Get widgets overlapping with the player.
	 *
	 * @method get_widgets_overlapped
	 * @return {jQuery} Returns a jQuery collection of HTMLElements.
	 */
	fn.get_widgets_overlapped = function () {
		var $widgets = $([]);
		var used = [];
		var rows_from_bottom = this.cells_occupied_by_player.rows.slice(0);
		rows_from_bottom.reverse();

		$.each(this.cells_occupied_by_player.cols, $.proxy(function (i, col) {
			$.each(rows_from_bottom, $.proxy(function (i, row) {
				// if there is a widget in the player position
				if (!this.gridmap[col]) {
					return true;
				} //next iteration
				var $w = this.gridmap[col][row];
				if (this.is_occupied(col, row) && !this.is_player($w) &&
						$.inArray($w, used) === -1
				) {
					$widgets = $widgets.add($w);
					used.push($w);
				}

			}, this));
		}, this));

		return $widgets;
	};


	/**
	 * This callback is executed when the player begins to collide with a column.
	 *
	 * @method on_start_overlapping_column
	 * @param {Number} col The collided column.
	 * @return {jQuery} Returns a jQuery collection of HTMLElements.
	 */
	fn.on_start_overlapping_column = function (col) {
		this.set_player(col, undefined , false);
	};


	/**
	 * A callback executed when the player begins to collide with a row.
	 *
	 * @method on_start_overlapping_row
	 * @param {Number} row The collided row.
	 * @return {jQuery} Returns a jQuery collection of HTMLElements.
	 */
	fn.on_start_overlapping_row = function (row) {
		this.set_player(undefined, row, false);
	};


	/**
	 * A callback executed when the the player ends to collide with a column.
	 *
	 * @method on_stop_overlapping_column
	 * @param {Number} col The collided row.
	 * @return {jQuery} Returns a jQuery collection of HTMLElements.
	 */
	fn.on_stop_overlapping_column = function (col) {
		//this.set_player(col, false);
		var self = this;
		if (this.options.shift_larger_widgets_down) {
			this.for_each_widget_below(col, this.cells_occupied_by_player.rows[0],
					function (tcol, trow) {
						self.move_widget_up(this, self.player_grid_data.size_y);
					});
		}
	};


	/**
	 * This callback is executed when the player ends to collide with a row.
	 *
	 * @method on_stop_overlapping_row
	 * @param {Number} row The collided row.
	 * @return {jQuery} Returns a jQuery collection of HTMLElements.
	 */
	fn.on_stop_overlapping_row = function (row) {
		//this.set_player(false, row);
		var self = this;
		var cols = this.cells_occupied_by_player.cols;
		if (this.options.shift_larger_widgets_down) {
			/*jshint -W083 */
			for (var c = 0, cl = cols.length; c < cl; c++) {
				this.for_each_widget_below(cols[c], row, function (tcol, trow) {
					self.move_widget_up(this, self.player_grid_data.size_y);
				});
			}
			/*jshint +W083 */
		}
	};

	//Not yet part of api - DM.
	fn.new_move_widget_to = function ($widget, col, row) {
		var widget_grid_data = $widget.coords().grid;

		this.remove_from_gridmap(widget_grid_data);
		widget_grid_data.row = row;
		widget_grid_data.col = col;

		this.add_to_gridmap(widget_grid_data);
		$widget.attr('data-row', row);
		$widget.attr('data-col', col);
		this.update_widget_position(widget_grid_data, $widget);
		this.$changed = this.$changed.add($widget);

		return this;
	};


	/**
	 * Move a widget to a specific row and column.
	 * If the widget has widgets below, all of these widgets will be moved also
	 *
	 * @method move_widget
	 * @param {HTMLElement} $widget The jQuery wrapped HTMLElement of the
	 * widget is going to be moved.
	 * @param {Number} new_col the column number to be set in widget
	 * @param {Number} new_row the row number to be set in widget
	 * @param {Function} callback is called when whole process is done.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.move_widget = function ($widget, new_col, new_row, callback) {
		var wgd = $widget.coords().grid;

		var new_grid_data = {
			col: new_col,
			row: new_row,
			size_x: wgd.size_x,
			size_y: wgd.size_y
		};

		this.mutate_widget_in_gridmap($widget, wgd, new_grid_data);

		this.set_dom_grid_height();
		this.set_dom_grid_width();

		if (callback) {
			callback.call(this, new_grid_data.col, new_grid_data.row);
		}

		return $widget;
	};


	/**
	 * Move a widget to a specific row. The cell or cells must be empty.
	 * If the widget has widgets below, all of these widgets will be moved also
	 * if they can.
	 *
	 * @method move_widget_to
	 * @param {HTMLElement} $widget The jQuery wrapped HTMLElement of the
	 * widget is going to be moved.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 * @param row - row to move the widget to
	 */
	fn.move_widget_to = function ($widget, row) {
		var self = this;
		var widget_grid_data = $widget.coords().grid;
		var $next_widgets = this.widgets_below($widget);

		var can_move_to_new_cell = this.can_move_to(
				widget_grid_data, widget_grid_data.col, row);

		if (can_move_to_new_cell === false) {
			return false;
		}

		this.remove_from_gridmap(widget_grid_data);
		widget_grid_data.row = row;
		this.add_to_gridmap(widget_grid_data);
		$widget.attr('data-row', row);
		this.$changed = this.$changed.add($widget);


		$next_widgets.each(function (i, widget) {
			var $w = $(widget);
			var wgd = $w.coords().grid;
			var can_go_up = self.can_go_widget_up(wgd);
			if (can_go_up && can_go_up !== wgd.row) {
				self.move_widget_to($w, can_go_up);
			}
		});

		return this;
	};


	/**
	 * Move up the specified widget and all below it.
	 *
	 * @method move_widget_up
	 * @param {HTMLElement} $widget The widget you want to move.
	 * @param {Number} [y_units] The number of cells that the widget has to move.
	 * @return {Boolean} Returns if the widget moved
	 */
	fn.move_widget_up = function ($widget, y_units) {
		if (y_units === undefined){
			return false;
		}
		var el_grid_data = $widget.coords().grid;
		var actual_row = el_grid_data.row;
		var moved = [];
		y_units || (y_units = 1);

		if (!this.can_go_up($widget)) {
			return false;
		} //break;

		this.for_each_column_occupied(el_grid_data, function (col) {
			// can_go_up
			if ($.inArray($widget, moved) === -1) {
				var widget_grid_data = $widget.coords().grid;
				var next_row = actual_row - y_units;
				next_row = this.can_go_up_to_row(
						widget_grid_data, col, next_row);

				if (!next_row) {
					return true;
				}

				this.remove_from_gridmap(widget_grid_data);
				widget_grid_data.row = next_row;
				this.add_to_gridmap(widget_grid_data);
				$widget.attr('data-row', widget_grid_data.row);
				this.$changed = this.$changed.add($widget);

				moved.push($widget);

				/* $next_widgets.each($.proxy(function(i, widget) {
				 console.log('from_within_move_widget_up');
				 this.move_widget_up($(widget), y_units);
				 }, this)); */
			}
		});

	};


	/**
	 * Move down the specified widget and all below it.
	 *
	 * @method move_widget_down
	 * @param {jQuery} $widget The jQuery object representing the widget
	 *  you want to move.
	 * @param {Number} y_units The number of cells that the widget has to move.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.move_widget_down = function ($widget, y_units) {
		var el_grid_data, actual_row, moved, y_diff;

		if (y_units <= 0) {
			return false;
		}

		el_grid_data = $widget.coords().grid;
		actual_row = el_grid_data.row;
		moved = [];
		y_diff = y_units;

		if (!$widget) {
			return false;
		}

		if ($.inArray($widget, moved) === -1) {

			var widget_grid_data = $widget.coords().grid;
			var next_row = actual_row + y_units;
			var $next_widgets = this.widgets_below($widget);

			this.remove_from_gridmap(widget_grid_data);

			$next_widgets.each($.proxy(function (i, widget) {
				var $w = $(widget);
				var wd = $w.coords().grid;
				var tmp_y = this.displacement_diff(
						wd, widget_grid_data, y_diff);

				if (tmp_y > 0) {
					this.move_widget_down($w, tmp_y);
				}
			}, this));

			widget_grid_data.row = next_row;
			this.update_widget_position(widget_grid_data, $widget);
			$widget.attr('data-row', widget_grid_data.row);
			this.$changed = this.$changed.add($widget);

			moved.push($widget);
		}
	};


	/**
	 * Check if the widget can move to the specified row, else returns the
	 * upper row possible.
	 *
	 * @method can_go_up_to_row
	 * @param {Number} widget_grid_data The current grid coords object of the
	 *  widget.
	 * @param {Number} col The target column.
	 * @param {Number} row The target row.
	 * @return {Boolean|Number} Returns the row number if the widget can move
	 *  to the target position, else returns false.
	 */
	fn.can_go_up_to_row = function (widget_grid_data, col, row) {
		var result = true;
		var urc = []; // upper_rows_in_columns
		var actual_row = widget_grid_data.row;
		var r;

		/* generate an array with columns as index and array with
		 * upper rows empty in the column */
		this.for_each_column_occupied(widget_grid_data, function (tcol) {
			urc[tcol] = [];

			r = actual_row;
			while (r--) {
				if (this.is_empty(tcol, r) && !this.is_placeholder_in(tcol, r)
				) {
					urc[tcol].push(r);
				} else {
					break;
				}
			}

			if (!urc[tcol].length) {
				result = false;
				return true;
			}

		});

		if (!result) {
			return false;
		}

		/* get common rows starting from upper position in all the columns
		 * that widget occupies */
		r = row;
		for (r = 1; r < actual_row; r++) {
			var common = true;

			for (var uc = 0, ucl = urc.length; uc < ucl; uc++) {
				if (urc[uc] && $.inArray(r, urc[uc]) === -1) {
					common = false;
				}
			}

			if (common === true) {
				result = r;
				break;
			}
		}

		return result;
	};


	fn.displacement_diff = function (widget_grid_data, parent_bgd, y_units) {
		var actual_row = widget_grid_data.row;
		var diffs = [];
		var parent_max_y = parent_bgd.row + parent_bgd.size_y;

		this.for_each_column_occupied(widget_grid_data, function (col) {
			var temp_y_units = 0;

			for (var r = parent_max_y; r < actual_row; r++) {
				if (this.is_empty(col, r)) {
					temp_y_units = temp_y_units + 1;
				}
			}

			diffs.push(temp_y_units);
		});

		var max_diff = Math.max.apply(Math, diffs);
		y_units = (y_units - max_diff);

		return y_units > 0 ? y_units : 0;
	};


	/**
	 * Get widgets below a widget.
	 *
	 * @method widgets_below
	 * @param {object} $el The jQuery wrapped HTMLElement.
	 * @return {jQuery} A jQuery collection of HTMLElements.
	 */
	fn.widgets_below = function ($el) {
		var $nexts = $([]);
		var el_grid_data = $.isPlainObject($el) ? $el : $el.coords().grid;
		if (el_grid_data === undefined) {
			//there is no grid, so we can't calculate the widgets below
			return $nexts;
		}
		var self = this;
		var next_row = el_grid_data.row + el_grid_data.size_y - 1;

		this.for_each_column_occupied(el_grid_data, function (col) {
			self.for_each_widget_below(col, next_row, function (tcol, trow) {
				if (!self.is_player(this) && $.inArray(this, $nexts) === -1) {
					$nexts = $nexts.add(this);
					return true; // break
				}
			});
		});

		return Gridster.sort_by_row_asc($nexts);
	};


	/**
	 * Update the array of mapped positions with the new player position.
	 *
	 * @method set_cells_player_occupies
	 * @param {Number} col The new player col.
	 * @param {Number} col The new player row.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 * @param row
	 */
	fn.set_cells_player_occupies = function (col, row) {
		this.remove_from_gridmap(this.placeholder_grid_data);
		this.placeholder_grid_data.col = col;
		this.placeholder_grid_data.row = row;
		this.add_to_gridmap(this.placeholder_grid_data, this.$player);
		return this;
	};


	/**
	 * Remove from the array of mapped positions the reference to the player.
	 *
	 * @method empty_cells_player_occupies
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.empty_cells_player_occupies = function () {
		this.remove_from_gridmap(this.placeholder_grid_data);
		return this;
	};

	fn.can_go_down = function ($el) {
		var can_go_down = true;
		var $gr = this;

		if ($el.hasClass(this.options.static_class)) {
			can_go_down = false;
		}

		this.widgets_below($el).each(function () {
			if ($(this).hasClass($gr.options.static_class)) {
				can_go_down = false;
			}
		});

		return can_go_down;
	};


	fn.can_go_up = function ($el) {
		var el_grid_data = $el.coords().grid;
		var initial_row = el_grid_data.row;
		var prev_row = initial_row - 1;

		var result = true;
		if (initial_row === 1) {
			return false;
		}

		this.for_each_column_occupied(el_grid_data, function (col) {
			if (this.is_occupied(col, prev_row) ||
					this.is_player(col, prev_row) ||
					this.is_placeholder_in(col, prev_row) ||
					this.is_player_in(col, prev_row)
			) {
				result = false;
				return true; //break
			}
		});

		return result;
	};


	/**
	 * Check if it's possible to move a widget to a specific col/row. It takes
	 * into account the dimensions (`size_y` and `size_x` attrs. of the grid
	 *  coords object) the widget occupies.
	 *
	 * @method can_move_to
	 * @param {Object} widget_grid_data The grid coords object that represents
	 *  the widget.
	 * @param {Object} col The col to check.
	 * @param {Object} row The row to check.
	 * @return {Boolean} Returns true if all cells are empty, else return false.
	 */
	fn.can_move_to = function (widget_grid_data, col, row) {
		var $w = widget_grid_data.el;
		var future_wd = {
			size_y: widget_grid_data.size_y,
			size_x: widget_grid_data.size_x,
			col: col,
			row: row
		};
		var result = true;

		//Prevents widgets go out of the grid, check only if the grid is not set to autogrow
		if (this.options.max_cols !== Infinity) {
			var right_col = col + widget_grid_data.size_x - 1;
			if (right_col > this.cols) {
				return false;
			}
		}

		if (this.options.max_rows < row + widget_grid_data.size_y - 1) {
			return false;
		}

		this.for_each_cell_occupied(future_wd, function (tcol, trow) {
			var $tw = this.is_widget(tcol, trow);
			if ($tw && (!widget_grid_data.el || $tw.is($w))) {
				result = false;
			}
		});

		return result;
	};


	/**
	 * Given the leftmost column returns all columns that are overlapping
	 *  with the player.
	 *
	 * @method get_targeted_columns
	 * @param {Number} [from_col] The leftmost column.
	 * @return {Array} Returns an array with column numbers.
	 */
	fn.get_targeted_columns = function (from_col) {
		var max = (from_col || this.player_grid_data.col) +
				(this.player_grid_data.size_x - 1);
		var cols = [];
		for (var col = from_col; col <= max; col++) {
			cols.push(col);
		}
		return cols;
	};


	/**
	 * Given the upper row returns all rows that are overlapping with the player.
	 *
	 * @method get_targeted_rows
	 * @param {Number} [from_row] The upper row.
	 * @return {Array} Returns an array with row numbers.
	 */
	fn.get_targeted_rows = function (from_row) {
		var max = (from_row || this.player_grid_data.row) +
				(this.player_grid_data.size_y - 1);
		var rows = [];
		for (var row = from_row; row <= max; row++) {
			rows.push(row);
		}
		return rows;
	};

	/**
	 * Get all columns and rows that a widget occupies.
	 *
	 * @method get_cells_occupied
	 * @param {Object} el_grid_data The grid coords object of the widget.
	 * @return {Object} Returns an object like `{ cols: [], rows: []}`.
	 */
	fn.get_cells_occupied = function (el_grid_data) {
		var cells = {cols: [], rows: []};
		var i;
		if (arguments[1] instanceof $) {
			el_grid_data = arguments[1].coords().grid;
		}

		for (i = 0; i < el_grid_data.size_x; i++) {
			var col = el_grid_data.col + i;
			cells.cols.push(col);
		}

		for (i = 0; i < el_grid_data.size_y; i++) {
			var row = el_grid_data.row + i;
			cells.rows.push(row);
		}

		return cells;
	};


	/**
	 * Iterate over the cells occupied by a widget executing a function for
	 * each one.
	 *
	 * @method for_each_cell_occupied
	 *  widget.
	 * @param grid_data
 * @param {Function} callback The function to execute on each column
	 *  iteration. Column and row are passed as arguments.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.for_each_cell_occupied = function (grid_data, callback) {
		this.for_each_column_occupied(grid_data, function (col) {
			this.for_each_row_occupied(grid_data, function (row) {
				callback.call(this, col, row);
			});
		});
		return this;
	};


	/**
	 * Iterate over the columns occupied by a widget executing a function for
	 * each one.
	 *
	 * @method for_each_column_occupied
	 * @param {Object} el_grid_data The grid coords object that represents
	 *  the widget.
	 * @param {Function} callback The function to execute on each column
	 *  iteration. The column number is passed as first argument.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.for_each_column_occupied = function (el_grid_data, callback) {
		for (var i = 0; i < el_grid_data.size_x; i++) {
			var col = el_grid_data.col + i;
			callback.call(this, col, el_grid_data);
		}
	};


	/**
	 * Iterate over the rows occupied by a widget executing a function for
	 * each one.
	 *
	 * @method for_each_row_occupied
	 * @param {Object} el_grid_data The grid coords object that represents
	 *  the widget.
	 * @param {Function} callback The function to execute on each column
	 *  iteration. The row number is passed as first argument.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.for_each_row_occupied = function (el_grid_data, callback) {
		for (var i = 0; i < el_grid_data.size_y; i++) {
			var row = el_grid_data.row + i;
			callback.call(this, row, el_grid_data);
		}
	};

	fn.clean_up_changed = function () {
		var $gr = this;
		$gr.$changed.each(function () {
			if ($gr.options.shift_larger_widgets_down) {
				$gr.move_widget_up($(this));
			}
		});
	};


	/**
	 * Traverse a series of widgets
	 * @param type - currently supported 'for_each'
	 * @param direction - The direction to traverse.  Supports 'above' and 'below'
	 *    NOTE: the directions are based on the layout in the grid above is toward the top
	 *    and below is toward the bottom. EG opposite direction of the row numbers
	 * @param col - column to traverse
	 * @param row - starting row in the column
	 * @param callback - a function that will be called for every widget found
	 * @private
	 */
	fn._traversing_widgets = function (type, direction, col, row, callback) {
		var ga = this.gridmap;
		if (!ga[col]) {
			return;
		}

		var cr, max;
		var action = type + '/' + direction;
		if (arguments[2] instanceof $) {
			var el_grid_data = arguments[2].coords().grid;
			col = el_grid_data.col;
			row = el_grid_data.row;
			callback = arguments[3];
		}
		var matched = [];
		var trow = row;


		var methods = {
			'for_each/above': function () {
				while (trow--) {
					if (trow > 0 && this.is_widget(col, trow) &&
							$.inArray(ga[col][trow], matched) === -1
					) {
						cr = callback.call(ga[col][trow], col, trow);
						matched.push(ga[col][trow]);
						if (cr) {
							break;
						}
					}
				}
			},
			'for_each/below': function () {
				for (trow = row + 1, max = ga[col].length; trow < max; trow++) {
					if (this.is_widget(col, trow) &&
							$.inArray(ga[col][trow], matched) === -1
					) {
						cr = callback.call(ga[col][trow], col, trow);
						matched.push(ga[col][trow]);
						//break was causing problems, leaving for testing.
						//if (cr) { break; }
					}
				}
			}
		};

		if (methods[action]) {
			methods[action].call(this);
		}
	};


	/**
	 * Iterate over each widget above the column and row specified.
	 *
	 * @method for_each_widget_above
	 * @param {Number} col The column to start iterating.
	 * @param {Number} row The row to start iterating.
	 * @param {Function} callback The function to execute on each widget
	 *  iteration. The value of `this` inside the function is the jQuery
	 *  wrapped HTMLElement.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.for_each_widget_above = function (col, row, callback) {
		this._traversing_widgets('for_each', 'above', col, row, callback);
		return this;
	};


	/**
	 * Iterate over each widget below the column and row specified.
	 *
	 * @method for_each_widget_below
	 * @param {Number} col The column to start iterating.
	 * @param {Number} row The row to start iterating.
	 * @param {Function} callback The function to execute on each widget
	 *  iteration. The value of `this` inside the function is the jQuery wrapped
	 *  HTMLElement.
	 * @return {Gridster} Returns the instance of the Gridster Class.
	 */
	fn.for_each_widget_below = function (col, row, callback) {
		this._traversing_widgets('for_each', 'below', col, row, callback);
		return this;
	};


	/**
	 * Returns the highest occupied cell in the grid.
	 *
	 * @method get_highest_occupied_cell
	 * @return {Object} Returns an object with `col` and `row` numbers.
	 */
	fn.get_highest_occupied_cell = function () {
		var r;
		var gm = this.gridmap;
		var rl = gm[1].length;
		var rows = [], cols = [];
		for (var c = gm.length - 1; c >= 1; c--) {
			for (r = rl - 1; r >= 1; r--) {
				if (this.is_widget(c, r)) {
					rows.push(r);
					cols.push(c);
					break;
				}
			}
		}

		return {
			col: Math.max.apply(Math, cols),
			row: Math.max.apply(Math, rows)
		};
	};

	/**
	 * return the widgets what exist within the given range of grid cells
	 * @param col1 - col of upper left search
	 * @param row1 - row of upper left search
	 * @param col2 - col of lower right search
	 * @param row2 - row of lower right search
	 * @returns {*|jQuery|HTMLElement} - a collection of the cells within the range
	 */
	fn.get_widgets_in_range = function (col1, row1, col2, row2) {
		var $widgets = $([]);
		var c, r, $w, wgd;

		for (c = col2; c >= col1; c--) {
			for (r = row2; r >= row1; r--) {
				$w = this.is_widget(c, r);

				if ($w !== false) {
					wgd = $w.data('coords').grid;
					if (wgd.col >= col1 && wgd.col <= col2 &&
							wgd.row >= row1 && wgd.row <= row2
					) {
						$widgets = $widgets.add($w);
					}
				}
			}
		}

		return $widgets;
	};

	/**
	 * return any widget which is located at the given coordinates
	 * @param col - col to search at
	 * @param row - row to search at
	 * @returns {*} - a collection of any widgets found.
	 */
	fn.get_widgets_at_cell = function (col, row) {
		return this.get_widgets_in_range(col, row, col, row);
	};

	/**
	 * gets the list of widgets in either the row or the col passed in_loop
	 *  Not sure if this makes sense for the API or what the use case is,
	 *   but I'm documenting it as it exists.
	 * @param col - a col to search for widgets from
	 * @param row - a row to search for widgets from
	 * @returns {*|jQuery|HTMLElement} - a collection of the widgets in either
	 * the row or the col passed in.
	 *
	 * @deprecated - if you want to search for widgets in a cell or a range
	 * look at get_widgets_in_range and get_widgets_at_cell
	 */
	fn.get_widgets_from = function (col, row) {
		var $widgets = $();

		if (col) {
			$widgets = $widgets.add(
					this.$widgets.filter(function () {
						var tcol = parseInt($(this).attr('data-col'));
						return (tcol === col || tcol > col);
					})
			);
		}

		if (row) {
			$widgets = $widgets.add(
					this.$widgets.filter(function () {
						var trow = parseInt($(this).attr('data-row'));
						return (trow === row || trow > row);
					})
			);
		}

		return $widgets;
	};


	/**
	 * Set the current height of the parent grid.
	 *
	 * @method set_dom_grid_height
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.set_dom_grid_height = function (height) {
		if (typeof height === 'undefined') {
			var r = this.get_highest_occupied_cell().row;
			height = ((r + 1) * this.options.widget_margins[1]) + (r * this.min_widget_height);
		}

		this.container_height = height;
		this.$el.css('height', this.container_height);
		return this;
	};

	/**
	 * Set the current width of the parent grid.
	 *
	 * @method set_dom_grid_width
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.set_dom_grid_width = function (cols) {
		if (typeof cols === 'undefined') {
			cols = this.get_highest_occupied_cell().col;
		}

		var max_cols = (this.options.max_cols === Infinity ? this.options.max_cols : this.cols);

		cols = Math.min(max_cols, Math.max(cols, this.options.min_cols));
		this.container_width = ((cols + 1) * this.options.widget_margins[0]) + (cols * this.min_widget_width);
		if (this.is_responsive()) {
			this.$el.css({'min-width': '100%', 'max-width': '100%'});
			return this; //if we are responsive exit before setting the width of $el
		}
		this.$el.css('width', this.container_width);

		return this;
	};


	/**
	 * Checks if this grid is responsive.
	 * autogenerate_stylesheet be true, the widget base width should be auto, and there must be a max_cols set.
	 * @returns {Boolean}
	 */
	fn.is_responsive = function () {
		return this.options.autogenerate_stylesheet && this.options.widget_base_dimensions[0] === 'auto' && this.options.max_cols !== Infinity;
	};

	/**
	 * Generates the width of the grid columns based on the width of the window.
	 * @returns {number}
	 */
	fn.get_responsive_col_width = function () {
		var cols = this.cols || this.options.max_cols;
		return (this.$el[0].scrollWidth - ((cols + 1) * this.options.widget_margins[0])) / cols;
	};

	/**
	 * Changes the minimum width of a widget based on the width of the window and the number of cols that can
	 * fit in it.
	 * @returns {Gridster}
	 */
	fn.resize_responsive_layout = function () {
		this.min_widget_width = this.get_responsive_col_width();
		this.generate_stylesheet();
		this.update_widgets_dimensions();
		this.drag_api.set_limits((this.cols * this.min_widget_width) + ((this.cols + 1) * this.options.widget_margins[0]));
		return this;
	};

	/**
	 * Switches between collapsed widgets the span the full width when the responsive_breakpoint is triggered.
	 * @param collapse
	 * @param opts
	 * @returns {Gridster}
	 */
	fn.toggle_collapsed_grid = function (collapse, opts) {
		if (collapse) {
			this.$widgets.css({
				'margin-top': opts.widget_margins[0],
				'margin-bottom': opts.widget_margins[0],
				'min-height': opts.widget_base_dimensions[1]
			});

			this.$el.addClass('collapsed');

			if (this.resize_api) {
				this.disable_resize();
			}

			if (this.drag_api) {
				this.disable();
			}
		} else {
			this.$widgets.css({
				'margin-top': 'auto',
				'margin-bottom': 'auto',
				'min-height': 'auto'
			});
			this.$el.removeClass('collapsed');
			if (this.resize_api) {
				this.enable_resize();
			}

			if (this.drag_api) {
				this.enable();
			}
		}
		return this;
	};

	/**
	 * It generates the necessary styles to position the widgets.
	 *
	 * @method generate_stylesheet
	 * @param {object} [opts] - set of gridster config options to generate stylessheets based on
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.generate_stylesheet = function (opts) {
		var styles = '';
		var i;
		var full_width = this.is_responsive() && this.options.responsive_breakpoint && ($(window).width() < this.options.responsive_breakpoint);

		opts || (opts = {});
		opts.cols || (opts.cols = this.cols);
		opts.rows || (opts.rows = this.rows);
		opts.namespace || (opts.namespace = this.options.namespace);
		opts.widget_base_dimensions ||
		(opts.widget_base_dimensions = this.options.widget_base_dimensions);

		opts.widget_margins || (opts.widget_margins = this.options.widget_margins);

		if (this.is_responsive()) {
			opts.widget_base_dimensions = [this.get_responsive_col_width(), opts.widget_base_dimensions[1]];
			this.toggle_collapsed_grid(full_width, opts);
		}

		// don't duplicate stylesheets for the same configuration
		var serialized_opts = $.param(opts);
		if ($.inArray(serialized_opts, Gridster.generated_stylesheets) >= 0) {
			return false;
		}

		this.generated_stylesheets.push(serialized_opts);
		Gridster.generated_stylesheets.push(serialized_opts);

		/* generate CSS styles for cols */
		for (i = 1; i <= opts.cols + 1; i++) {
			styles += (opts.namespace + ' [data-col="' + i + '"] { left:' +
			(full_width ? this.options.widget_margins[0] :
					((i * opts.widget_margins[0]) + ((i - 1) * opts.widget_base_dimensions[0]))) + 'px; }\n');
		}

		/* generate CSS styles for rows */
		for (i = 1; i <= opts.rows + 1; i++) {
			styles += (opts.namespace + ' [data-row="' + i + '"] { top:' +
			((i * opts.widget_margins[1]) + ((i - 1) * opts.widget_base_dimensions[1])) + 'px; }\n');
		}

		for (var y = 1; y <= opts.rows; y++) {
			styles += (opts.namespace + ' [data-sizey="' + y + '"] { height:' +
			(full_width ? 'auto' : ((y * opts.widget_base_dimensions[1]) + ((y - 1) * opts.widget_margins[1]))) + (full_width ? '' : 'px') + '; }\n');

		}

		for (var x = 1; x <= opts.cols; x++) {
			var colWidth = ((x * opts.widget_base_dimensions[0]) + ((x - 1) * opts.widget_margins[0]));
			styles += (opts.namespace + ' [data-sizex="' + x + '"] { width:' +
			(full_width ? (this.$wrapper.width() - this.options.widget_margins[0] * 2) : colWidth > this.$wrapper.width() ? this.$wrapper.width() : colWidth ) + 'px; }\n');

		}

		this.remove_style_tags();

		return this.add_style_tag(styles);
	};


	/**
	 * Injects the given CSS as string to the head of the document.
	 *
	 * @method add_style_tag
	 * @param {String} css The styles to apply.
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.add_style_tag = function (css) {
		var d = document;
		var cssID = 'gridster-stylesheet';
		if (this.options.namespace !== '') {
			cssID = cssID + '-' + this.options.namespace;
		}

		if (!document.getElementById(cssID)) {
			var tag = d.createElement('style');
			tag.id = cssID;

			d.getElementsByTagName('head')[0].appendChild(tag);
			tag.setAttribute('type', 'text/css');

			if (tag.styleSheet) {
				tag.styleSheet.cssText = css;
			} else {
				tag.appendChild(document.createTextNode(css));
			}

			this.remove_style_tags();
			this.$style_tags = this.$style_tags.add(tag);
		}

		return this;
	};


	/**
	 * Remove the style tag with the associated id from the head of the document
	 *
	 * @method  remove_style_tag
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.remove_style_tags = function () {
		var all_styles = Gridster.generated_stylesheets;
		var ins_styles = this.generated_stylesheets;

		this.$style_tags.remove();

		Gridster.generated_stylesheets = $.map(all_styles, function (s) {
			if ($.inArray(s, ins_styles) === -1) {
				return s;
			}
		});
	};


	/**
	 * Generates a faux grid to collide with it when a widget is dragged and
	 * detect row or column that we want to go.
	 *
	 * @method generate_faux_grid
	 * @param {Number} rows Number of columns.
	 * @param {Number} cols Number of rows.
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.generate_faux_grid = function (rows, cols) {
		this.faux_grid = [];
		this.gridmap = [];
		var col;
		var row;
		for (col = cols; col > 0; col--) {
			this.gridmap[col] = [];
			for (row = rows; row > 0; row--) {
				this.add_faux_cell(row, col);
			}
		}
		return this;
	};


	/**
	 * Add cell to the faux grid.
	 *
	 * @method add_faux_cell
	 * @param {Number} row The row for the new faux cell.
	 * @param {Number} col The col for the new faux cell.
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.add_faux_cell = function (row, col) {
		var coords = $({
			left: this.baseX + ((col - 1) * this.min_widget_width),
			top: this.baseY + (row - 1) * this.min_widget_height,
			width: this.min_widget_width,
			height: this.min_widget_height,
			col: col,
			row: row,
			original_col: col,
			original_row: row
		}).coords();

		if (!$.isArray(this.gridmap[col])) {
			this.gridmap[col] = [];
		}

		if (typeof this.gridmap[col][row] === 'undefined') {
			this.gridmap[col][row] = false;
		}
		this.faux_grid.push(coords);

		return this;
	};


	/**
	 * Add rows to the faux grid.
	 *
	 * @method add_faux_rows
	 * @param {Number} rows The number of rows you want to add to the faux grid.
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.add_faux_rows = function (rows) {
		rows = window.parseInt(rows, 10);

		var actual_rows = this.rows;
		var max_rows = actual_rows + parseInt(rows || 1);

		for (var r = max_rows; r > actual_rows; r--) {
			for (var c = this.cols; c >= 1; c--) {
				this.add_faux_cell(r, c);
			}
		}

		this.rows = max_rows;

		if (this.options.autogenerate_stylesheet) {
			this.generate_stylesheet();
		}

		return this;
	};

	/**
	 * Add cols to the faux grid.
	 *
	 * @method add_faux_cols
	 * @param {Number} cols The number of cols you want to add to the faux grid.
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.add_faux_cols = function (cols) {
		cols = window.parseInt(cols, 10);

		var actual_cols = this.cols;
		var max_cols = actual_cols + parseInt(cols || 1);
		max_cols = Math.min(max_cols, this.options.max_cols);

		for (var c = actual_cols + 1; c <= max_cols; c++) {
			for (var r = this.rows; r >= 1; r--) {
				this.add_faux_cell(r, c);
			}
		}

		this.cols = max_cols;

		if (this.options.autogenerate_stylesheet) {
			this.generate_stylesheet();
		}

		return this;
	};


	/**
	 * Recalculates the offsets for the faux grid. You need to use it when
	 * the browser is resized.
	 *
	 * @method recalculate_faux_grid
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.recalculate_faux_grid = function () {
		var aw = this.$wrapper.width();
		this.baseX = ($window.width() - aw) / 2;
		this.baseY = this.$wrapper.offset().top;

		if (this.$wrapper.css('position') === 'relative') {
			this.baseX = this.baseY = 0;
		}

		$.each(this.faux_grid, $.proxy(function (i, coords) {
			this.faux_grid[i] = coords.update({
				left: this.baseX + (coords.data.col - 1) * this.min_widget_width,
				top: this.baseY + (coords.data.row - 1) * this.min_widget_height
			});
		}, this));

		if (this.is_responsive()) {
			this.resize_responsive_layout();
		}

		if (this.options.center_widgets) {
			this.center_widgets();
		}

		return this;
	};


	/**
	 * Resize dimensions of widgets in grid based on given options
	 *
	 * @method resize_widget_dimensions
	 * @param options
	 * @returns {Gridster}
	 */
	fn.resize_widget_dimensions = function (options) {
		if (options.widget_margins) {
			this.options.widget_margins = options.widget_margins;
		}

		if (options.widget_base_dimensions) {
			this.options.widget_base_dimensions = options.widget_base_dimensions;
		}

		this.$widgets.each($.proxy(function (i, widget) {
			var $widget = $(widget);
			this.resize_widget($widget);
		}, this));

		this.generate_grid_and_stylesheet();
		this.get_widgets_from_DOM();
		this.set_dom_grid_height();
		this.set_dom_grid_width();

		return this;
	};


	/**
	 * Get all widgets in the DOM and register them.
	 *
	 * @method get_widgets_from_DOM
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.get_widgets_from_DOM = function () {
		var widgets_coords = this.$widgets.map($.proxy(function (i, widget) {
			var $w = $(widget);
			return this.dom_to_coords($w);
		}, this));

		widgets_coords = Gridster.sort_by_row_and_col_asc(widgets_coords);

		var changes = $(widgets_coords).map($.proxy(function (i, wgd) {
			return this.register_widget(wgd) || null;
		}, this));

		if (changes.length) {
			this.$el.trigger('gridster:positionschanged');
		}

		return this;
	};

	fn.get_num_widgets = function () {
		return this.$widgets.size();
	};

	/**
	 * Helper function used to set the current number of columns in the grid
	 *
	 * @param wrapper_width
	 */
	fn.set_num_columns = function (wrapper_width) {

		var max_cols = this.options.max_cols;

		var cols = Math.floor(wrapper_width / (this.min_widget_width + this.options.widget_margins[0])) +
				this.options.extra_cols;

		var actual_cols = this.$widgets.map(function () {
			return $(this).attr('data-col');
		}).get();

		//needed to pass tests with phantomjs
		actual_cols.length || (actual_cols = [0]);

		var min_cols = Math.max.apply(Math, actual_cols);

		this.cols = Math.max(min_cols, cols, this.options.min_cols);

		if (max_cols !== Infinity && max_cols >= min_cols && max_cols < this.cols) {
			this.cols = max_cols;
		}

		if (this.drag_api) {
			this.drag_api.set_limits((this.cols * this.min_widget_width) + ((this.cols + 1) * this.options.widget_margins[0]));
		}
	};


	/**
	 * Calculate columns and rows to be set based on the configuration
	 *  parameters, grid dimensions, etc ...
	 *
	 * @method generate_grid_and_stylesheet
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.generate_grid_and_stylesheet = function () {
		var aw = this.$wrapper.width();

		this.set_num_columns(aw);

		// get all rows that could be occupied by the current widgets
		var max_rows = this.options.extra_rows;
		this.$widgets.each(function (i, w) {
			max_rows += (+$(w).attr('data-sizey'));
		});

		//this.rows = Math.max(max_rows, this.options.min_rows);
		this.rows = this.options.max_rows;

		this.baseX = ($window.width() - aw) / 2;
		this.baseY = this.$wrapper.offset().top;

		if (this.options.autogenerate_stylesheet) {
			this.generate_stylesheet();
		}

		return this.generate_faux_grid(this.rows, this.cols);
	};

	/**
	 * Destroy this gridster by removing any sign of its presence, making it easy to avoid memory leaks
	 *
	 * @method destroy
	 * @param {Boolean} remove If true, remove gridster from DOM.
	 * @return {Object} Returns the instance of the Gridster class.
	 */
	fn.destroy = function (remove) {
		this.$el.removeData('gridster');

		// remove coords from elements
		$.each(this.$widgets, function () {
			$(this).removeData('coords');
		});

		// remove bound callback on window resize
		$window.unbind('.gridster');

		if (this.drag_api) {
			this.drag_api.destroy();
		}
		if (this.resize_api) {
			this.resize_api.destroy();
		}

		this.$widgets.each(function (i, el) {
			$(el).coords().destroy();
		});

		if (this.resize_api) {
			this.resize_api.destroy();
		}

		this.remove_style_tags();

		remove && this.$el.remove();

		return this;
	};


	//jQuery adapter
	$.fn.gridster = function (options) {
		return this.each(function () {
			var $this = $(this);
			if (!$this.data('gridster')) {
				$this.data('gridster', new Gridster(this, options));
			}
		});
	};

	return Gridster;

}));
