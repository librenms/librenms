describe('Map pane selection', function() {
    /**
     * Avoid as much as possible creating and destroying objects for each test.
     * Instead, try re-using them, except for the ones under test of course.
     * PhantomJS does not perform garbage collection for the life of the page,
     * i.e. during the entire test process (Karma runs all tests in a single page).
     * http://stackoverflow.com/questions/27239708/how-to-get-around-memory-error-with-karma-phantomjs
     *
     * The `beforeEach` and `afterEach do not seem to cause much issue.
     * => they can still be used to initialize some setup between each test.
     * Using them keeps a readable spec/index.
     *
     * But refrain from re-creating div and map every time. Re-use those objects.
     */

    /////////////////////////////
    // SETUP FOR EACH TEST
    /////////////////////////////

    beforeEach(function () {

        // Nothing for this test suite.

    });

    afterEach(function () {

        if (group instanceof L.MarkerClusterGroup) {
            group.clearLayers();
            map.removeLayer(group);
        }

        // Throw away group as it can be assigned with different configurations between tests.
        group = null;

    });


    /////////////////////////////
    // PREPARATION CODE
    /////////////////////////////

    var div, map, group;

    div = document.createElement('div');
    div.style.width = '200px';
    div.style.height = '200px';
    document.body.appendChild(div);

    map = L.map(div, { maxZoom: 18 });

    // Create map pane
    map.createPane('testPane');

    // Corresponds to zoom level 8 for the above div dimensions.
    map.fitBounds(new L.LatLngBounds([
        [1, 1],
        [2, 2]
    ]));


    /////////////////////////////
    // TESTS
    /////////////////////////////

    it('recognizes and applies option', function() {
        group = new L.MarkerClusterGroup({clusterPane: 'testPane'});

        var marker = new L.Marker([1.5, 1.5]);
        var marker2 = new L.Marker([1.5, 1.5]);

        group.addLayers([marker, marker2]);
        map.addLayer(group);

        expect(map._panes.testPane.childNodes.length).to.be(1);
    });

    it('defaults to default marker pane', function() {
        group = new L.MarkerClusterGroup();

        var marker = new L.Marker([1.5, 1.5]);
        var marker2 = new L.Marker([1.5, 1.5]);

        group.addLayers([marker, marker2]);
        map.addLayer(group);

        expect(map._panes[L.Marker.prototype.options.pane].childNodes.length).to.be(1);
    });

    /////////////////////////////
    // CLEAN UP CODE
    /////////////////////////////

    map.remove();
    document.body.removeChild(div);

});