var pkg = require( './package.json' );

var assetBanner =	'/*!\n' +
					' * ' + pkg.name + ' (' + pkg.homepage + ')\n' +
					' * By ' + pkg.author.name + ' (' + pkg.author.url + ')\n' +
					' * Licensed under ' + pkg.license + '\n' +
					' */\n';

var gulp = require( 'gulp' );

var gutil = require( 'gulp-util' );
var rename = require( 'gulp-rename' );
var replace = require( 'gulp-replace' );
var sort = require( 'gulp-sort' );
var banner = require( 'gulp-banner' );
var sass = require( 'gulp-sass' );
var csscomb = require( 'gulp-csscomb' );
var cleanCss = require( 'gulp-clean-css' );
var concat = require( 'gulp-concat' );
var jshint = require( 'gulp-jshint' );
var uglify = require( 'gulp-uglify' );

var paths = {
	php: {
		files: [ './*.php', './inc/**/*.php' ]
	},
	sass: {
		files: [ './assets/src/sass/**/*.scss' ],
		src: './assets/src/sass/',
		dst: './assets/dist/css/'
	},
	js: {
		files: [ './assets/src/js/**/*.js' ],
		src: './assets/src/js/',
		dst: './assets/dist/js/'
	}
};

/* ---- MAIN TASKS ---- */

// general task (compile Sass and JavaScript and refresh POT file)
gulp.task( 'default', [ 'sass', 'js' ]);

// watch Sass and JavaScript files
gulp.task( 'watch', function() {
	gulp.watch( paths.sass.files, [ 'sass' ]);
	gulp.watch( paths.js.files, [ 'js' ]);
});

/* ---- SUB TASKS ---- */

// compile Sass
gulp.task( 'sass', function( done ) {
	gulp.src( paths.sass.files )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'expanded'
		}) )
		.pipe( csscomb() )
		.pipe( banner( assetBanner ) )
		.pipe( gulp.dest( paths.sass.dst ) )
		.pipe( cleanCss({
			keepSpecialComments: 0
		}) )
		.pipe( banner( assetBanner ) )
		.pipe( rename({
			extname: '.min.css'
		}) )
		.pipe( gulp.dest( paths.sass.dst ) )
		.on( 'end', done );
});

// compile JavaScript
gulp.task( 'js', function( done ) {
	gulp.src( paths.js.files )
		.pipe( jshint({
			lookup: true
		}) )
		.pipe( banner( assetBanner ) )
		.pipe( gulp.dest( paths.js.dst ) )
		.pipe( uglify() )
		.pipe( banner( assetBanner ) )
		.pipe( rename({
			extname: '.min.js'
		}) )
		.pipe( gulp.dest( paths.js.dst ) )
		.on( 'end', done );
});
