var deps = {

	Core: {
		src: ['MarkerClusterGroup.js',
		      'MarkerCluster.js',
		      'MarkerOpacity.js',
		      'DistanceGrid.js'],
		desc: 'The core of the library.'
	},

	QuickHull: {
		src: ['MarkerCluster.QuickHull.js'],
		desc: 'ConvexHull generation. Used to show the area outline of the markers within a cluster.',
		heading: 'QuickHull'
	},

	Spiderfier: {
		src: ['MarkerCluster.Spiderfier.js'],
		desc: 'Provides the ability to show all of the child markers of a cluster.',
		heading: 'Spiderfier'
	},

	Refresh: {
		src: ['MarkerClusterGroup.Refresh.js'],
		desc: 'Method to request refreshing of clusters icon to reflect changes in markers data.',
		heading: 'Refresh'
	}
};

if (typeof exports !== 'undefined') {
	exports.deps = deps;
}
