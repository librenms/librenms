Gridster.js
===========

Gridster is a jQuery plugin that makes building intuitive draggable
layouts from elements spanning multiple columns. You can even
dynamically add and remove elements from the grid.

More at [http://gridster.net/](http://gridster.net/).

License
=======

Distributed under the MIT license.

Whodunit
========

Gridster is built by [Ducksboard](http://ducksboard.com/).

dustmoo Modifications
===========

Changelog 11-26-2012

Reworked swapping functionality to better handle large to small widget handling.

---

Widgets of smaller or equal size to the dragged widget (player) 
will swap places with the original widget. 

This causes tiles to swap left and right as well as up and down.

By default smaller players will shift larger widgets down.

I have added an option to prevent this behavior:

	$.gridster({
		shift_larger_widgets_down: false
	});

By setting shift_larger_widgets_down to false, smaller widgets will not displace larger ones.

