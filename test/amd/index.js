/*global Gridster:false*/
/*global chai:false, describe:false, beforeEach:false, afterEach:false, it:false*/

require.config({
    baseUrl : '../../',

    paths: {
        mocha: 'node_modules/mocha/mocha',
        chai: 'node_modules/chai/chai',
        jquery: 'node_modules/jquery/dist/jquery',
        gridster: 'dist/jquery.gridster'
    }

});

require(['jquery'], function($) {
    $.noConflict( true );

    require(['test/amd/index'], function(Gridster) {
        mocha.setup('bdd');

        $(function() {
            mocha.run();
        });
    });
});


define(['chai', 'jquery', 'gridster'], function(chai, $, Gridster) {
    'use strict';

    var expect = chai.expect;

    describe('AMD support', function() {
        describe('Gridster', function() {
            it('should not define jQuery as global', function() {
                expect(window.$).to.be.undefined;
                expect(window.jQuery).to.be.undefined;
            });

            it('should not define Gridster as global', function() {
                expect(window.Gridster).to.be.undefined;
                expect(window.GridsterDraggable).to.be.undefined;
                expect(window.GridsterCoords).to.be.undefined;
                expect(window.GridsterCollision).to.be.undefined;
            });

            it('should return Gridster class', function() {
                expect(Gridster).to.be.a('function');
                expect(Gridster.name).to.equal('Gridster');
            });

            it('should define the jquery bridge', function() {
                expect($.fn.gridster).to.be.a('function');
            });
        });

        describe('Draggable', function() {
            var Draggable = require('gridster-draggable');

            it('should not be defined in the global scope', function() {
                expect(window.GridsterDraggable).to.be.undefined;
            });

            it('should return the Draggable class', function() {
                expect(Draggable.name).to.equal('Draggable');
            });
        });
    });

});
