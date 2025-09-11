const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setPublicPath('html/')
    .js('resources/js/app.js', 'js')
    .vue()
    .sass('resources/sass/app.scss', 'css/vendor.css')
    .postCss('resources/css/app.css', 'css', [
        require('tailwindcss'),
    ])
    .extract()
    .version('html/js/lang/*.js');
