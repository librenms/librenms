
L.DistanceGrid = function (cellSize) {
	this._cellSize = cellSize;
	this._sqCellSize = cellSize * cellSize;
	this._grid = {};
};

L.DistanceGrid.prototype = {

	addObject: function (obj, point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    grid = this._grid,
		    row = grid[y] = grid[y] || {},
		    cell = row[x] = row[x] || [];

		obj._dGridCell = cell;
		obj._dGridPoint = point;

		cell.push(obj);
	},

	updateObject: function (obj, point) {
		this.removeObject(obj);
		this.addObject(obj, point);
	},

	removeObject: function (obj) {
		var oldCell = obj._dGridCell,
		    point = obj._dGridPoint,
		    i, len, x, y;

		for (i = 0, len = oldCell.length; i < len; i++) {
			if (oldCell[i] === obj) {

				oldCell.splice(i, 1);

				if (len === 1) {
					x = this._getCoord(point.x);
					y = this._getCoord(point.y);
					delete this._grid[y][x];
				}

				break;
			}
		}
	},

	eachObject: function (fn, context) {
		var i, j, k, len, row, cell, removed,
		    grid = this._grid;

		for (i in grid) {
			if (grid.hasOwnProperty(i)) {
				row = grid[i];

				for (j in row) {
					if (row.hasOwnProperty(j)) {
						cell = row[j];

						for (k = 0, len = cell.length; k < len; k++) {
							removed = fn.call(context, cell[k]);
							if (removed) {
								k--;
								len--;
							}
						}
					}
				}
			}
		}
	},

	getNearObject: function (point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    i, j, k, row, cell, len, obj;

		for (i = y - 1; i <= y + 1; i++) {
			row = this._grid[i];
			if (row) {

				for (j = x - 1; j <= x + 1; j++) {
					cell = row[j];
					if (cell) {

						for (k = 0, len = cell.length; k < len; k++) {
							obj = cell[k];
							if (this._sqDist(obj._dGridPoint, point) < this._sqCellSize) {
								return obj;
							}
						}
					}
				}
			}
		}

		return null;
	},

	_getCoord: function (x) {
		return Math.floor(x / this._cellSize);
	},

	_sqDist: function (p, p2) {
		var dx = p2.x - p.x,
		    dy = p2.y - p.y;
		return dx * dx + dy * dy;
	}
};
