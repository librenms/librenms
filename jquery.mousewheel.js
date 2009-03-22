/*! Copyright (c) 2008 Brandon Aaron (http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 *
 * Version: 3.0.1
 * 
 * Requires: 1.2.2+
 */

(function($) {

$.event.special.mousewheel = {
	setup: function() {
		var handler = $.event.special.mousewheel.handler;
	
		if ( this.addEventListener ) {
			this.addEventListener( 'DOMMouseScroll', handler, false);
			this.addEventListener( 'mousewheel', handler, false);
		} else
			this.onmousewheel = handler;
	},
	
	teardown: function() {
		var handler = $.event.special.mousewheel.handler;
		
		if ( this.removeEventListener ) {
			this.removeEventListener( 'DOMMouseScroll', handler, false);
			this.removeEventListener( 'mousewheel', handler, false);
		} else
			this.onmousewheel = null;
	},
	
	handler: function(event) {
		var args = [].slice.call( arguments, 1 );
		
		event = $.event.fix(event || window.event);
		event.currentTarget = this;
		var delta = 0, returnValue = true;
		
		if ( event.wheelDelta ) delta = event.wheelDelta/120;
		if ( event.detail     ) delta = -event.detail/3;
		
		event.data = event.data || {};
		event.type = "mousewheel";
		
		// Add delta to the front of the arguments
		args.unshift(delta);
		// Add event to the front of the arguments
		args.unshift(event);

		return $.event.handle.apply(this, args);
	}
};

$.fn.extend({
	mousewheel: function(fn) {
		return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
	},
	
	unmousewheel: function(fn) {
		return this.unbind("mousewheel", fn);
	}
});

})(jQuery);