// grab our gulp packages
var gulp       = require('gulp');
var uglify     = require('gulp-uglify');
var sass       = require('gulp-sass');
var concat     = require('gulp-concat');
var cleancss   = require('gulp-clean-css');
var ngAnnotate = require('gulp-ng-annotate');
var plumber    = require('gulp-plumber');
var gulpcopy   = require('gulp-copy');
var autoprefixer = require('gulp-autoprefixer');

var sources = {
  'js': {
		"public": [
      "./sources/js/public/libs/*.js",
			"./sources/js/public/*.js",
		],
		'admin': [
			"./sources/js/admin/*.js",
		],
		'admin-libs': [
			"./sources/js/admin/libs/*.js",
		],
		'admin-post': [
			"./sources/js/admin-post/*.js",
		],
		'admin-post-libs': [
      "./node_modules/flatpickr/dist/flatpickr.js",
			"./sources/js/admin-post/libs/*.js",
		],
		'settings': [
			"./sources/js/settings/*.js",
		],
		'settings-libs': [
      "./node_modules/angular/angular.min.js",
      "./node_modules/ng-sortable/dist/ng-sortable.js",
			"./node_modules/underscore/underscore-min.js",
			"./sources/js/settings/libs/*.js",
		],
		'dashboard': [
			"./sources/js/dashboard/*.js",
		],
		'dashboard-libs': [
			"./node_modules/angular/angular.min.js",
			"./node_modules/underscore/underscore-min.js",
			"./sources/js/dashboard/libs/*.js",
		],
  },
  'css': {
    'public': "./sources/scss/public/",
		'admin': "./sources/scss/admin/",
		'admin-post': "./sources/scss/admin-post/",
    'settings': "./sources/scss/settings/",
    'dashboard': "./sources/scss/dashboard/",
  }
}
var dist = "./assets/";

// Build minimised assets JS, CSS
gulp.task('build', function(){
  for (const key in sources.js) {
    build_js( key );
  };
  for (const key in sources.css) {
    build_css( key );
  };
  return;
});

/** JS BUILDING */
function build_js( type ){
  console.log( ':: bulding js > '+type );
  var result = gulp.src(sources.js[type])
    .pipe(plumber())
    .pipe(concat(type+'.min.js'))
    .pipe(ngAnnotate())
    .pipe(uglify())
    .pipe(gulp.dest( dist + 'js/' ));
  return result;
};
for (const key in sources.js) {
  console.log( ':: prepare task > build-'+key+'-js' );
  gulp.task('build-' + key + '-js', function() {
    return build_js( key );
  });
};

/** CSS BUILD */
function build_css( type ){
  console.log( ':: bulding css > '+type );
  var result = gulp.src(sources.css[type] + type + '.scss')
    .pipe(plumber())
    .pipe(sass())
    .pipe(cleancss())
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(gulp.dest( dist + 'css/' ));
  return result;
};
for (const key in sources.css) {
  console.log( ':: prepare task > build-'+key+'-css' );
  gulp.task('build-' + key + '-css', function() {
    return build_css( key );
  });
};

/** FONT COPY */
gulp.task('font-copy', function () {
  return gulp.src("./node_modules/@mdi/font/fonts/*")
    .pipe(gulpcopy('./assets/fonts/', {
      prefix: 4
    }));
});

gulp.task('watch', function() {
  // js
  for (const key in sources.js) {
    console.log(':: watch > '+ 'build-'+key+'-js');
    gulp.watch( sources.js[key], gulp.series('build-'+key+'-js') );
  }
  // css
  for (const key in sources.css) {
    console.log(':: watch > '+ 'build-'+key+'-css');
    gulp.watch( sources.css[key] + '**', gulp.series('build-'+key+'-css') );
  }
});

gulp.task('default', gulp.series('watch'));