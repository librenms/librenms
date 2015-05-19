//Based on https://github.com/jonnyreeves/qunit-require
/* global require, QUnit*/
'use strict';
require.config({
    //set the baseUrl to the src dir so that gridster
    //AMD modules can be found.
    baseUrl: '../src/',
    paths: {
        'QUnit': '../libs/qunit/qunit/qunit',
        'jquery': '../libs/jquery/dist/jquery',
        'gridster': 'jquery.gridster'
    },
    map: {
      // '*' means all modules will get 'jquery-private'
      // for their 'jquery' dependency.
      '*': { 'jquery': '../test/jquery-private' },

      // 'jquery-private' wants the real jQuery module
      // though. If this line was not here, there would
      // be an unresolvable cyclic dependency.
      '../test/jquery-private': { 'jquery': 'jquery' }
    },
    shim: {
       'QUnit': {
           exports: 'QUnit',
           init: function() {
               QUnit.config.autoload = false;
               QUnit.config.autostart = false;
           }
       }
    }
});
/*
    Load all of our require'd files

    We have to load all of the gridster jquery.* modules so
    that they are defined for when gridster needs them.

    Lastly, load the testsuite which defines some tests.
*/
require([
    'QUnit',
    'utils',
    'jquery.coords',
    'jquery.collision',
    'jquery.draggable',
    '../test/testsuite'
    //Require'd files are passed as args, but we don't use them.
], function(QUnit/*, utils, coords, collision, draggable, testsuite*/) {
        QUnit.load();
        QUnit.start();
    }
);
