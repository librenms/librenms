/*
  Leaflet.AwesomeMarkers, a plugin that adds colorful iconic markers for Leaflet, based on the Font Awesome icons
  (c) 2012-2013, Lennard Voogdt

  http://leafletjs.com
  https://github.com/lvoogdt
*/
(function (window, document, undefined) {
/*
 * Leaflet.AwesomeMarkers assumes that you have already included the Leaflet library.
 */

L.AwesomeMarkers = {};

L.AwesomeMarkers.version = '1.0';

L.AwesomeMarkers.Icon = L.Icon.extend({
  options: {
    iconSize: [35, 45], 
    iconAnchor:   [17, 42],
    popupAnchor: [1, -32],
    shadowAnchor: [10, 12],
    icon: 'font', // All the font-awesome icons are possible
    shadowSize: [36, 16],
    className: 'awesome-marker',
    color: 'blue' // red, orange, green, blue, purple
  },

  initialize: function (options) {
    options = L.setOptions(this, options);
  },

  createIcon: function () {
    var div = document.createElement('div'),
        options = this.options;

    if (options.icon) {
      div.innerHTML = this._createInner();
    }

    if (options.bgPos) {
      div.style.backgroundPosition =
              (-options.bgPos.x) + 'px ' + (-options.bgPos.y) + 'px';
    }

    this._setIconStyles(div, 'icon-' + options.color);
    return div;
  },

  _createInner: function() {
    return "<i class='icon-" + this.options.icon + (this.options.spin ? " icon-spin'" :"'") + "></i>";
  },

  _setIconStyles: function (img, name) {
    var options = this.options,
        size = L.point(options[name + 'Size']),
        anchor;

    if (name === 'shadow') {
      anchor = L.point(options.shadowAnchor || options.iconAnchor);
    } else {
      anchor = L.point(options.iconAnchor);
    }

    if (!anchor && size) {
      anchor = size.divideBy(2, true);
    }

    img.className = 'awesome-marker-' + name + ' ' + options.className;

    if (anchor) {
      img.style.marginLeft = (-anchor.x) + 'px';
      img.style.marginTop  = (-anchor.y) + 'px';
    }

    if (size) {
      img.style.width  = size.x + 'px';
      img.style.height = size.y + 'px';
    }
  },

  createShadow: function () {
    var div = document.createElement('div'),
        options = this.options;

    this._setIconStyles(div, 'shadow');
    return div;
  }
});
    
L.AwesomeMarkers.icon = function (options) {
  return new L.AwesomeMarkers.Icon(options);
};

}(this, document));



