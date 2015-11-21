'use strict';
define([
    'QUnit',
    'jquery',
    'gridster'
], function(QUnit, $, gridster) {

        QUnit.module('Gridster AMD', {
            setup: function () {
            },
            teardown: function () {
            }
        });

        QUnit.test('window.$ should be undefined.', function() {
            equal(typeof window.$, 'undefined', 'window.$ should be undefined');
            equal(typeof window.jQuery, 'undefined', 'window.jQuery should be undefined');
        });


        QUnit.test('gridster should be initialized.', function() {
            $('.wrapper ul').gridster();
            equal($('.wrapper').hasClass('ready'), true, 'Gridster should initialized wrapper.');
            equal($('.wrapper ul li').length, $('.gs-w').length, 'grid elements get a .gs-w class');
        });
    }
);
