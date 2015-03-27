// GRID COMMON TYPE EXTENSIONS
// ============

$.fn.extend({
    _bgAria: function (name, value)
    {
        return this.attr("aria-" + name, value);
    },

    _bgBusyAria: function(busy)
    {
        return (busy == null || busy) ? 
            this._bgAria("busy", "true") : 
            this._bgAria("busy", "false");
    },

    _bgRemoveAria: function (name)
    {
        return this.removeAttr("aria-" + name);
    },

    _bgEnableAria: function (enable)
    {
        return (enable == null || enable) ? 
            this.removeClass("disabled")._bgAria("disabled", "false") : 
            this.addClass("disabled")._bgAria("disabled", "true");
    },

    _bgEnableField: function (enable)
    {
        return (enable == null || enable) ? 
            this.removeAttr("disabled") : 
            this.attr("disabled", "disable");
    },

    _bgShowAria: function (show)
    {
        return (show == null || show) ? 
            this.show()._bgAria("hidden", "false") :
            this.hide()._bgAria("hidden", "true");
    },

    _bgSelectAria: function (select)
    {
        return (select == null || select) ? 
            this.addClass("active")._bgAria("selected", "true") : 
            this.removeClass("active")._bgAria("selected", "false");
    },

    _bgId: function (id)
    {
        return (id) ? this.attr("id", id) : this.attr("id");
    }
});

if (!String.prototype.resolve)
{
    var formatter = {
        "checked": function(value)
        {
            if (typeof value === "boolean")
            {
                return (value) ? "checked=\"checked\"" : "";
            }
            return value;
        }
    };

    String.prototype.resolve = function (substitutes, prefixes)
    {
        var result = this;
        $.each(substitutes, function (key, value)
        {
            if (value != null && typeof value !== "function")
            {
                if (typeof value === "object")
                {
                    var keys = (prefixes) ? $.extend([], prefixes) : [];
                    keys.push(key);
                    result = result.resolve(value, keys) + "";
                }
                else
                {
                    if (formatter && formatter[key] && typeof formatter[key] === "function")
                    {
                        value = formatter[key](value);
                    }
                    key = (prefixes) ? prefixes.join(".") + "." + key : key;
                    var pattern = new RegExp("\\{\\{" + key + "\\}\\}", "gm");
                    result = result.replace(pattern, (value.replace) ? value.replace(/\$/gi, "&#36;") : value);
                }
            }
        });
        return result;
    };
}

if (!Array.prototype.first)
{
    Array.prototype.first = function (condition)
    {
        for (var i = 0; i < this.length; i++)
        {
            var item = this[i];
            if (condition(item))
            {
                return item;
            }
        }
        return null;
    };
}

if (!Array.prototype.contains)
{
    Array.prototype.contains = function (condition)
    {
        for (var i = 0; i < this.length; i++)
        {
            var item = this[i];
            if (condition(item))
            {
                return true;
            }
        }
        return false;
    };
}

if (!Array.prototype.page)
{
    Array.prototype.page = function (page, size)
    {
        var skip = (page - 1) * size,
            end = skip + size;
        return (this.length > skip) ? 
            (this.length > end) ? this.slice(skip, end) : 
                this.slice(skip) : [];
    };
}

if (!Array.prototype.where)
{
    Array.prototype.where = function (condition)
    {
        var result = [];
        for (var i = 0; i < this.length; i++)
        {
            var item = this[i];
            if (condition(item))
            {
                result.push(item);
            }
        }
        return result;
    };
}

if (!Array.prototype.propValues)
{
    Array.prototype.propValues = function (propName)
    {
        var result = [];
        for (var i = 0; i < this.length; i++)
        {
            result.push(this[i][propName]);
        }
        return result;
    };
}