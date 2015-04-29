// GRID PUBLIC CLASS DEFINITION
// ====================

/**
 * Represents the jQuery Bootgrid plugin.
 *
 * @class Grid
 * @constructor
 * @param element {Object} The corresponding DOM element.
 * @param options {Object} The options to override default settings.
 * @chainable
 **/
var Grid = function(element, options)
{
    this.element = $(element);
    this.origin = this.element.clone();
    this.options = $.extend(true, {}, Grid.defaults, this.element.data(), options);
    // overrides rowCount explicitly because deep copy ($.extend) leads to strange behaviour
    var rowCount = this.options.rowCount = this.element.data().rowCount || options.rowCount || this.options.rowCount;
    this.columns = [];
    this.current = 1;
    this.currentRows = [];
    this.identifier = null; // The first column ID that is marked as identifier
    this.selection = false;
    this.converter = null; // The converter for the column that is marked as identifier
    this.rowCount = ($.isArray(rowCount)) ? rowCount[0] : rowCount;
    this.rows = [];
    this.searchPhrase = "";
    this.selectedRows = [];
    this.sort = {};
    this.total = 0;
    this.totalPages = 0;
    this.cachedParams = {
        lbl: this.options.labels,
        css: this.options.css,
        ctx: {}
    };
    this.header = null;
    this.footer = null;
    this.xqr = null;

    // todo: implement cache
};

/**
 * An object that represents the default settings.
 * There are two ways to override the sub-properties.
 * Either by doing it generally (global) or on initialization.
 *
 * @static
 * @class defaults
 * @for Grid
 * @example
 *   // Global approach
 *   $.bootgrid.defaults.selection = true;
 * @example
 *   // Initialization approach
 *   $("#bootgrid").bootgrid({ selection = true });
 **/
Grid.defaults = {
    navigation: 3, // it's a flag: 0 = none, 1 = top, 2 = bottom, 3 = both (top and bottom)
    padding: 2, // page padding (pagination)
    columnSelection: true,
    rowCount: [10, 25, 50, -1], // rows per page int or array of int (-1 represents "All")

    /**
     * Enables row selection (to enable multi selection see also `multiSelect`). Default value is `false`.
     *
     * @property selection
     * @type Boolean
     * @default false
     * @for defaults
     * @since 1.0.0
     **/
    selection: false,

    /**
     * Enables multi selection (`selection` must be set to `true` as well). Default value is `false`.
     *
     * @property multiSelect
     * @type Boolean
     * @default false
     * @for defaults
     * @since 1.0.0
     **/
    multiSelect: false,

    /**
     * Enables entire row click selection (`selection` must be set to `true` as well). Default value is `false`.
     *
     * @property rowSelect
     * @type Boolean
     * @default false
     * @for defaults
     * @since 1.1.0
     **/
    rowSelect: false,

    /**
     * Defines whether the row selection is saved internally on filtering, paging and sorting 
     * (even if the selected rows are not visible).
     *
     * @property keepSelection
     * @type Boolean
     * @default false
     * @for defaults
     * @since 1.1.0
     **/
    keepSelection: false,

    highlightRows: false, // highlights new rows (find the page of the first new row)
    sorting: true,
    multiSort: false,
    ajax: false, // todo: find a better name for this property to differentiate between client-side and server-side data

    /**
     * Enriches the request object with additional properties. Either a `PlainObject` or a `Function` 
     * that returns a `PlainObject` can be passed. Default value is `{}`.
     *
     * @property post
     * @type Object|Function
     * @default function (request) { return request; }
     * @for defaults
     * @deprecated Use instead `requestHandler`
     **/
    post: {}, // or use function () { return {}; } (reserved properties are "current", "rowCount", "sort" and "searchPhrase")

    /**
     * Sets the data URL to a data service (e.g. a REST service). Either a `String` or a `Function` 
     * that returns a `String` can be passed. Default value is `""`.
     *
     * @property url
     * @type String|Function
     * @default ""
     * @for defaults
     **/
    url: "", // or use function () { return ""; }

    /**
     * Defines whether the search is case sensitive or insensitive.
     *
     * @property caseSensitive
     * @type Boolean
     * @default true
     * @for defaults
     * @since 1.1.0
     **/
    caseSensitive: true,

    // note: The following properties should not be used via data-api attributes

    /**
     * Transforms the JSON request object in what ever is needed on the server-side implementation.
     *
     * @property requestHandler
     * @type Function
     * @default function (request) { return request; }
     * @for defaults
     * @since 1.1.0
     **/
    requestHandler: function (request) { return request; },

    /**
     * Transforms the response object into the expected JSON response object.
     *
     * @property responseHandler
     * @type Function
     * @default function (response) { return response; }
     * @for defaults
     * @since 1.1.0
     **/
    responseHandler: function (response) { return response; },

    /**
     * A list of converters.
     *
     * @property converters
     * @type Object
     * @for defaults
     * @since 1.0.0
     **/
    converters: {
        numeric: {
            from: function (value) { return +value; }, // converts from string to numeric
            to: function (value) { return value + ""; } // converts from numeric to string
        },
        string: {
            // default converter
            from: function (value) { return value; },
            to: function (value) { return value; }
        }
    },

    /**
     * Contains all css classes.
     *
     * @property css
     * @type Object
     * @for defaults
     **/
    css: {
        actions: "actions btn-group", // must be a unique class name or constellation of class names within the header and footer
        center: "text-center",
        columnHeaderAnchor: "column-header-anchor", // must be a unique class name or constellation of class names within the column header cell
        columnHeaderText: "text",
        dropDownItem: "dropdown-item", // must be a unique class name or constellation of class names within the actionDropDown,
        dropDownItemButton: "dropdown-item-button", // must be a unique class name or constellation of class names within the actionDropDown
        dropDownItemCheckbox: "dropdown-item-checkbox", // must be a unique class name or constellation of class names within the actionDropDown
        dropDownMenu: "dropdown btn-group", // must be a unique class name or constellation of class names within the actionDropDown
        dropDownMenuItems: "dropdown-menu pull-right", // must be a unique class name or constellation of class names within the actionDropDown
        dropDownMenuText: "dropdown-text", // must be a unique class name or constellation of class names within the actionDropDown
        footer: "bootgrid-footer container-fluid",
        header: "bootgrid-header container-fluid",
        icon: "icon glyphicon",
        iconColumns: "glyphicon-th-list",
        iconDown: "glyphicon-chevron-down",
        iconRefresh: "glyphicon-refresh",
        iconUp: "glyphicon-chevron-up",
        infos: "infos", // must be a unique class name or constellation of class names within the header and footer,
        left: "text-left",
        pagination: "pagination", // must be a unique class name or constellation of class names within the header and footer
        paginationButton: "button", // must be a unique class name or constellation of class names within the pagination

        /**
         * CSS class to select the parent div which activates responsive mode.
         *
         * @property responsiveTable
         * @type String
         * @default "table-responsive"
         * @for css
         * @since 1.1.0
         **/
        responsiveTable: "table-responsive",

        right: "text-right",
        search: "search form-group", // must be a unique class name or constellation of class names within the header and footer
        searchField: "search-field form-control",
        selectBox: "select-box", // must be a unique class name or constellation of class names within the entire table
        selectCell: "select-cell", // must be a unique class name or constellation of class names within the entire table

        /**
         * CSS class to highlight selected rows.
         *
         * @property selected
         * @type String
         * @default "active"
         * @for css
         * @since 1.1.0
         **/
        selected: "active",

        sortable: "sortable",
        table: "bootgrid-table table"
    },

    /**
     * A dictionary of formatters.
     *
     * @property formatters
     * @type Object
     * @for defaults
     * @since 1.0.0
     **/
    formatters: {},

    /**
     * Contains all labels.
     *
     * @property labels
     * @type Object
     * @for defaults
     **/
    labels: {
        all: "All",
        infos: "Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries",
        loading: "Loading...",
        noResults: "No results found!",
        refresh: "Refresh",
        search: "Search"
    },

    /**
     * Contains all templates.
     *
     * @property templates
     * @type Object
     * @for defaults
     **/
    templates: {
        actionButton: "<button class=\"btn btn-default\" type=\"button\" title=\"{{ctx.text}}\">{{ctx.content}}</button>",
        actionDropDown: "<div class=\"{{css.dropDownMenu}}\"><button class=\"btn btn-default dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><span class=\"{{css.dropDownMenuText}}\">{{ctx.content}}</span> <span class=\"caret\"></span></button><ul class=\"{{css.dropDownMenuItems}}\" role=\"menu\"></ul></div>",
        actionDropDownItem: "<li><a href=\"{{ctx.uri}}\" class=\"{{css.dropDownItem}} {{css.dropDownItemButton}}\">{{ctx.text}}</a></li>",
        actionDropDownCheckboxItem: "<li><label class=\"{{css.dropDownItem}}\"><input name=\"{{ctx.name}}\" type=\"checkbox\" value=\"1\" class=\"{{css.dropDownItemCheckbox}}\" {{ctx.checked}} /> {{ctx.label}}</label></li>",
        actions: "<div class=\"{{css.actions}}\"></div>",
        body: "<tbody></tbody>",
        cell: "<td class=\"{{ctx.css}}\">{{ctx.content}}</td>",
        footer: "<div id=\"{{ctx.id}}\" class=\"{{css.footer}}\"><div class=\"row\"><div class=\"col-sm-6\"><p class=\"{{css.pagination}}\"></p></div><div class=\"col-sm-6 infoBar\"><p class=\"{{css.infos}}\"></p></div></div></div>",
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\"><div class=\"col-sm-12 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div></div>",
        headerCell: "<th data-column-id=\"{{ctx.column.id}}\" class=\"{{ctx.css}}\"><a href=\"javascript:void(0);\" class=\"{{css.columnHeaderAnchor}} {{ctx.sortable}}\"><span class=\"{{css.columnHeaderText}}\">{{ctx.column.text}}</span>{{ctx.icon}}</a></th>",
        icon: "<span class=\"{{css.icon}} {{ctx.iconCss}}\"></span>",
        infos: "<div class=\"{{css.infos}}\">{{lbl.infos}}</div>",
        loading: "<tr><td colspan=\"{{ctx.columns}}\" class=\"loading\">{{lbl.loading}}</td></tr>",
        noResults: "<tr><td colspan=\"{{ctx.columns}}\" class=\"no-results\">{{lbl.noResults}}</td></tr>",
        pagination: "<ul class=\"{{css.pagination}}\"></ul>",
        paginationItem: "<li class=\"{{ctx.css}}\"><a href=\"{{ctx.uri}}\" class=\"{{css.paginationButton}}\">{{ctx.text}}</a></li>",
        rawHeaderCell: "<th class=\"{{ctx.css}}\">{{ctx.content}}</th>", // Used for the multi select box
        row: "<tr{{ctx.attr}}>{{ctx.cells}}</tr>",
        search: "<div class=\"{{css.search}}\"><div class=\"input-group\"><span class=\"{{css.icon}} input-group-addon glyphicon-search\"></span> <input type=\"text\" class=\"{{css.searchField}}\" placeholder=\"{{lbl.search}}\" /></div></div>",
        select: "<input name=\"select\" type=\"{{ctx.type}}\" class=\"{{css.selectBox}}\" value=\"{{ctx.value}}\" {{ctx.checked}} />"
    }
};

/**
 * Appends rows.
 *
 * @method append
 * @param rows {Array} An array of rows to append
 * @chainable
 **/
Grid.prototype.append = function(rows)
{
    if (this.options.ajax)
    {
        // todo: implement ajax DELETE
    }
    else
    {
        var appendedRows = [];
        for (var i = 0; i < rows.length; i++)
        {
            if (appendRow.call(this, rows[i]))
            {
                appendedRows.push(rows[i]);
            }
        }
        sortRows.call(this);
        highlightAppendedRows.call(this, appendedRows);
        loadData.call(this);
        this.element.trigger("appended" + namespace, [appendedRows]);
    }

    return this;
};

/**
 * Removes all rows.
 *
 * @method clear
 * @chainable
 **/
Grid.prototype.clear = function()
{
    if (this.options.ajax)
    {
        // todo: implement ajax POST
    }
    else
    {
        var removedRows = $.extend([], this.rows);
        this.rows = [];
        this.current = 1;
        this.total = 0;
        loadData.call(this);
        this.element.trigger("cleared" + namespace, [removedRows]);
    }

    return this;
};

/**
 * Removes the control functionality completely and transforms the current state to the initial HTML structure.
 *
 * @method destroy
 * @chainable
 **/
Grid.prototype.destroy = function()
{
    // todo: this method has to be optimized (the complete initial state must be restored)
    $(window).off(namespace);
    if (this.options.navigation & 1)
    {
        this.header.remove();
    }
    if (this.options.navigation & 2)
    {
        this.footer.remove();
    }
    this.element.before(this.origin).remove();

    return this;
};

/**
 * Resets the state and reloads rows.
 *
 * @method reload
 * @chainable
 **/
Grid.prototype.reload = function()
{
    this.current = 1; // reset
    loadData.call(this);

    return this;
};

/**
 * Removes rows by ids. Removes selected rows if no ids are provided.
 *
 * @method remove
 * @param [rowsIds] {Array} An array of rows ids to remove
 * @chainable
 **/
Grid.prototype.remove = function(rowIds)
{
    if (this.identifier != null)
    {
        var that = this;

        if (this.options.ajax)
        {
            // todo: implement ajax DELETE
        }
        else
        {
            rowIds = rowIds || this.selectedRows;
            var id,
                removedRows = [];

            for (var i = 0; i < rowIds.length; i++)
            {
                id = rowIds[i];

                for (var j = 0; j < this.rows.length; j++)
                {
                    if (this.rows[j][this.identifier] === id)
                    {
                        removedRows.push(this.rows[j]);
                        this.rows.splice(j, 1);
                        break;
                    }
                }
            }

            this.current = 1; // reset
            loadData.call(this);
            this.element.trigger("removed" + namespace, [removedRows]);
        }
    }

    return this;
};

/**
 * Searches in all rows for a specific phrase (but only in visible cells).
 *
 * @method search
 * @param phrase {String} The phrase to search for
 * @chainable
 **/
Grid.prototype.search = function(phrase)
{
    if (this.searchPhrase !== phrase)
    {
        this.current = 1;
        this.searchPhrase = phrase;
        loadData.call(this);
    }

    return this;
};

/**
 * Selects rows by ids. Selects all visible rows if no ids are provided.
 * In server-side scenarios only visible rows are selectable.
 *
 * @method select
 * @param [rowsIds] {Array} An array of rows ids to select
 * @chainable
 **/
Grid.prototype.select = function(rowIds)
{
    if (this.selection)
    {
        rowIds = rowIds || this.currentRows.propValues(this.identifier);

        var id, i, 
            selectedRows = [];

        while (rowIds.length > 0 && !(!this.options.multiSelect && selectedRows.length === 1))
        {
            id = rowIds.pop();
            if ($.inArray(id, this.selectedRows) === -1)
            {
                for (i = 0; i < this.currentRows.length; i++)
                {
                    if (this.currentRows[i][this.identifier] === id)
                    {
                        selectedRows.push(this.currentRows[i]);
                        this.selectedRows.push(id);
                        break;
                    }
                }
            }
        }

        if (selectedRows.length > 0)
        {
            var selectBoxSelector = getCssSelector(this.options.css.selectBox),
                selectMultiSelectBox = this.selectedRows.length >= this.currentRows.length;

            i = 0;
            while (!this.options.keepSelection && selectMultiSelectBox && i < this.currentRows.length)
            {
                selectMultiSelectBox = ($.inArray(this.currentRows[i++][this.identifier], this.selectedRows) !== -1);
            }
            this.element.find("thead " + selectBoxSelector).prop("checked", selectMultiSelectBox);

            if (!this.options.multiSelect)
            {
                this.element.find("tbody > tr " + selectBoxSelector + ":checked")
                    .trigger("click" + namespace);
            }

            for (i = 0; i < this.selectedRows.length; i++)
            {
                this.element.find("tbody > tr[data-row-id=\"" + this.selectedRows[i] + "\"]")
                    .addClass(this.options.css.selected)._bgAria("selected", "true")
                    .find(selectBoxSelector).prop("checked", true);
            }

            this.element.trigger("selected" + namespace, [selectedRows]);
        }
    }

    return this;
};

/**
 * Deselects rows by ids. Deselects all visible rows if no ids are provided.
 * In server-side scenarios only visible rows are deselectable.
 *
 * @method deselect
 * @param [rowsIds] {Array} An array of rows ids to deselect
 * @chainable
 **/
Grid.prototype.deselect = function(rowIds)
{
    if (this.selection)
    {
        rowIds = rowIds || this.currentRows.propValues(this.identifier);

        var id, i, pos,
            deselectedRows = [];

        while (rowIds.length > 0)
        {
            id = rowIds.pop();
            pos = $.inArray(id, this.selectedRows);
            if (pos !== -1)
            {
                for (i = 0; i < this.currentRows.length; i++)
                {
                    if (this.currentRows[i][this.identifier] === id)
                    {
                        deselectedRows.push(this.currentRows[i]);
                        this.selectedRows.splice(pos, 1);
                        break;
                    }
                }
            }
        }

        if (deselectedRows.length > 0)
        {
            var selectBoxSelector = getCssSelector(this.options.css.selectBox);

            this.element.find("thead " + selectBoxSelector).prop("checked", false);
            for (i = 0; i < deselectedRows.length; i++)
            {
                this.element.find("tbody > tr[data-row-id=\"" + deselectedRows[i][this.identifier] + "\"]")
                    .removeClass(this.options.css.selected)._bgAria("selected", "false")
                    .find(selectBoxSelector).prop("checked", false);
            }
            
            this.element.trigger("deselected" + namespace, [deselectedRows]);
        }
    }

    return this;
};


/**
 * Sorts rows.
 *
 * @method sort
 * @param dictionary {Object} A dictionary which contains the sort information
 * @chainable
 **/
Grid.prototype.sort = function(dictionary)
{
    var values = (dictionary) ? $.extend({}, dictionary) : {};
    if (values === this.sort)
    {
        return this;
    }

    this.sort = values;

    renderTableHeader.call(this);
    sortRows.call(this);
    loadData.call(this);

    return this;
};