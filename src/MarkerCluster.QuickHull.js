/* Copyright (c) 2012 the authors listed at the following URL, and/or
the authors of referenced articles or incorporated external code:
http://en.literateprograms.org/Quickhull_(Javascript)?action=history&offset=20120410175256

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Retrieved from: http://en.literateprograms.org/Quickhull_(Javascript)?oldid=18434
*/

(function () {
	L.QuickHull = {
		getDistant: function (cpt, bl) {
			var Vy = bl[1][0] - bl[0][0];
			var Vx = bl[0][1] - bl[1][1];
			return (Vx * (cpt[0] - bl[0][0]) + Vy * (cpt[1] - bl[0][1]))
		},


		findMostDistantPointFromBaseLine: function (baseLine, points) {
			var maxD = 0;
			var maxPt = new Array();
			var newPoints = new Array();
			for (var idx in points) {
				var pt = points[idx];
				var d = this.getDistant(pt, baseLine);

				if (d > 0) {
					newPoints.push(pt);
				} else {
					continue;
				}

				if (d > maxD) {
					maxD = d;
					maxPt = pt;
				}

			}
			return { 'maxPoint': maxPt, 'newPoints': newPoints }
		},

		buildConvexHull: function (baseLine, points) {
			var convexHullBaseLines = new Array();
			var t = this.findMostDistantPointFromBaseLine(baseLine, points);
			if (t.maxPoint.length) { // if there is still a point "outside" the base line
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([baseLine[0], t.maxPoint], t.newPoints)
					);
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([t.maxPoint, baseLine[1]], t.newPoints)
					);
				return convexHullBaseLines;
			} else {  // if there is no more point "outside" the base line, the current base line is part of the convex hull
				return [baseLine];
			}
		},

		getConvexHull: function (points) {
			//find first baseline
			var maxX, minX;
			var maxPt, minPt;
			for (var idx in points) {
				var pt = points[idx];
				if (pt[0] > maxX || !maxX) {
					maxPt = pt;
					maxX = pt[0];
				}
				if (pt[0] < minX || !minX) {
					minPt = pt;
					minX = pt[0];
				}
			}
			var ch = [].concat(this.buildConvexHull([minPt, maxPt], points),
								this.buildConvexHull([maxPt, minPt], points))
			return ch;
		}
	}
}());

L.MarkerCluster.include({
	getConvexHull: function () {
		var childMarkers = this.getAllChildMarkers(),
			points = [],
			hullLatLng = [],
			hull, p, i;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			p = childMarkers[i].getLatLng();
			points.push([p.lat, p.lng]);
		}

		hull = L.QuickHull.getConvexHull(points);

		for (i = 0; i < hull.length; i++) {
			p = hull[i];
			hullLatLng.push(new L.LatLng(p[0][0], p[0][1]));
		}

		return hullLatLng;
	}
});