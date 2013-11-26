// cycle between divs in a set, from: http://jquery.malsup.com/cycle/basic.html (see also http://jsfiddle.net/n1ck/4PvU7/)
$(document).ready(function() {
    $('.boxes').cycle({
	timeout: 9000,
	speed:   1000,
	random:  1,
	pause:   1,
        fx: 'scrollUp' // choose your transition type, ex: fade, scrollUp, shuffle, etc...
    });
});
