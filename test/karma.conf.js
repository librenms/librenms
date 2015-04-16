module.exports = function(config) {
    'use strict';

    config.set({
        frameworks: ['mocha', 'chai'],

        client: {
            captureConsole: true,
            mocha: {
                reporter: 'html', // change Karma's debug.html to the mocha web reporter
                ui: 'bdd'
            }
        },

        files: [
            {pattern: 'node_modules/jquery/dist/jquery.js', include: true},
            {pattern: 'dist/jquery.gridster.css', include: true},
            {pattern: 'dist/jquery.gridster.js', include: true},
            {pattern: 'test/lib/test.css', include: true},
            'test/index.js'
        ],

        reporters: ['mocha'],

        // level of logging (config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG)
        logLevel: config.LOG_DEBUG,

        // enable / disable watching file and executing tests whenever any file changes
        autoWatch: true,

        colors: true,

        // Start these browsers, currently available:
        // Chrome, ChromeCanary, Firefox, Opera, Safari (only Mac), PhantomJS, IE (only Windows)
        browsers: ['Chrome'],

        singleRun: true
    });
};