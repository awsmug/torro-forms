/* ---- THE FOLLOWING CONFIG SHOULD BE EDITED ---- */

var pkg = require( './package.json' );

var config = {
	pluginSlug: 'torro-forms',
	pluginName: 'Torro Forms',
	pluginURI: pkg.homepage,
	author: pkg.author.name,
	authorURI: pkg.author.url,
	description: pkg.description,
	version: pkg.version,
	license: pkg.license.name,
	licenseURI: pkg.license.url,
	tags: pkg.keywords.join( ', ' ),
	contributors: [ 'mahype', 'flixos90', 'awesome-ug' ].join( ', ' ),
	minRequired: '4.4',
	testedUpTo: '4.7.3',
	translateURI: 'https://translate.wordpress.org/projects/wp-plugins/torro-forms'
};

/* ---- DO NOT EDIT BELOW THIS LINE ---- */

// WP plugin header for main plugin file
var pluginheader = 	'Plugin Name: ' + config.pluginName + '\n' +
					'Plugin URI:  ' + config.pluginURI + '\n' +
					'Description: ' + config.description + '\n' +
					'Version:     ' + config.version + '\n' +
					'Author:      ' + config.author + '\n' +
					'Author URI:  ' + config.authorURI + '\n' +
					'License:     ' + config.license + '\n' +
					'License URI: ' + config.licenseURI + '\n' +
					'Text Domain: ' + config.pluginSlug + '\n' +
					'Tags:        ' + config.tags;

// WP plugin header for readme.txt
var readmeheader =	'Plugin Name:       ' + config.pluginName + '\n' +
					'Plugin URI:        ' + config.pluginURI + '\n' +
					'Author:            ' + config.author + '\n' +
					'Author URI:        ' + config.authorURI + '\n' +
					'Contributors:      ' + config.contributors + '\n' +
					'Requires at least: ' + config.minRequired + '\n' +
					'Tested up to:      ' + config.testedUpTo + '\n' +
					'Stable tag:        ' + config.version + '\n' +
					'Version:           ' + config.version + '\n' +
					'License:           ' + config.license + '\n' +
					'License URI:       ' + config.licenseURI + '\n' +
					'Tags:              ' + config.tags;

// header for minified assets
var assetheader =	'/*!\n' +
					' * ' + config.pluginName + ' Version ' + config.version + ' (' + config.pluginURI + ')\n' +
					' * Licensed under ' + config.license + ' (' + config.licenseURI + ')\n' +
					' */\n';


/* ---- REQUIRED DEPENDENCIES ---- */

var gulp = require( 'gulp' );

var sass = require( 'gulp-sass' );
var csscomb = require( 'gulp-csscomb' );
var minifyCSS = require( 'gulp-minify-css' );
var jshint = require( 'gulp-jshint' );
var concat = require( 'gulp-concat' );
var uglify = require( 'gulp-uglify' );
var gutil = require( 'gulp-util' );
var rename = require( 'gulp-rename' );
var replace = require( 'gulp-replace' );
var sort = require( 'gulp-sort' );
var banner = require( 'gulp-banner' );
var composer = require( 'gulp-composer' );
var bower = require( 'bower' );

var paths = {
	php: {
		files: [ './*.php', './components/**/*.php', './core/**/*.php', './includes/**/*.php', './templates/**/*.php' ]
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
gulp.task( 'default', [ 'sass', 'js' ]);

// watch Sass and JavaScript files
gulp.task( 'watch', function() {
	gulp.watch( paths.sass.files, [ 'sass' ]);
	gulp.watch( paths.js.files, [ 'js' ]);
});

// build the plugin
gulp.task( 'build', [ 'version-replace', 'readme-replace' ], function() {
	gulp.start( 'header-replace' );
	gulp.start( 'default' );
});

/* ---- SUB TASKS ---- */

// compile Sass
gulp.task( 'sass', function( done ) {
	gulp.src( paths.sass.files )
		.pipe( sass({
			errLogToConsole: true
		}) )
		.pipe( csscomb() )
		.pipe( banner( assetheader ) )
		.pipe( gulp.dest( paths.sass.dst ) )
		.pipe( minifyCSS({
			keepSpecialComments: 0
		}) )
		.pipe( banner( assetheader ) )
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
		.pipe( banner( assetheader ) )
		.pipe( gulp.dest( paths.js.dst ) )
		.pipe( uglify() )
		.pipe( banner( assetheader ) )
		.pipe( rename({
			extname: '.min.js'
		}) )
		.pipe( gulp.dest( paths.js.dst ) )
		.on( 'end', done );
});

// replace the version header in all PHP files
gulp.task( 'version-replace', function( done ) {
	gulp.src( paths.php.files, { base: './' })
		.pipe( replace( /\* @version ([^\s])+/, '* @version ' + config.version ) )
		.pipe( gulp.dest( './' ) )
		.on( 'end', done );
});

// replace the plugin header in the main plugin file
gulp.task( 'header-replace', function( done ) {
	gulp.src( './' + config.pluginSlug + '.php' )
		.pipe( replace( /((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/, '/*\n' + pluginheader + '\n*/' ) )
		.pipe( gulp.dest( './' ) )
		.on( 'end', done );
});

// replace the plugin header in readme.txt
gulp.task( 'readme-replace', function( done ) {
	gulp.src( './readme.txt' )
		.pipe( replace( /\=\=\= (.+) \=\=\=([\s\S]+)\=\= Description \=\=/m, '=== ' + config.pluginName + ' ===\n\n' + readmeheader + '\n\n' + config.description + '\n\n== Description ==' ) )
		.pipe( gulp.dest( './' ) )
		.on( 'end', done );
});

// install Bower components
gulp.task( 'bower-install', function() {
	return bower.commands.install()
		.on( 'log', function( data ) {
			gutil.log( 'bower', gutil.colors.cyan( data.id ), data.message );
		});
});
