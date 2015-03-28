// GRID PLUGIN DEFINITION
// =====================

var old = $.fn.bootgrid;

$.fn.bootgrid = function (option)
{
    var args = Array.prototype.slice.call(arguments, 1);
    return this.each(function ()
    {
        var $this = $(this),
            instance = $this.data(namespace),
            options = typeof option === "object" && option;

        if (!instance && option === "destroy")
        {
            return;
        }
        if (!instance)
        {
            $this.data(namespace, (instance = new Grid(this, options)));
            init.call(instance);
        }
        if (typeof option === "string")
        {
            return instance[option].apply(instance, args);
        }
    });
};

$.fn.bootgrid.Constructor = Grid;

// GRID NO CONFLICT
// ===============

$.fn.bootgrid.noConflict = function ()
{
    $.fn.bootgrid = old;
    return this;
};

// GRID DATA-API
// ============

$("[data-toggle=\"bootgrid\"]").bootgrid();