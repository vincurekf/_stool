const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setResourceRoot('../../');
mix.config.fileLoaderDirs.fonts = 'assets/fonts';
mix.config.fileLoaderDirs.images = 'assets/img';

mix
//.js('sources/js/public/public.js', 'assets/js')
.js('sources/js/admin.js', 'assets/js')
.js('sources/js/admin-post.js', 'assets/js')
.js('sources/js/dashboard.js', 'assets/js')
.js('sources/js/settings.js', 'assets/js')
.sass('sources/scss/admin.scss', 'assets/css')
.sass('sources/scss/admin-post.scss', 'assets/css')
.sass('sources/scss/dashboard.scss', 'assets/css')
.sass('sources/scss/widgets.scss', 'assets/css')
.sass('sources/scss/settings.scss', 'assets/css');
