/* ---- THE FOLLOWING CONFIG SHOULD BE EDITED ---- */

var pkg = require( './package.json' );

function parseKeywords( keywords ) {
	// These keywords are useful for Packagist/NPM/Bower, but not for the WordPress plugin repository.
	var disallowed = [ 'wordpress', 'plugin' ];

	k = keywords;
	for ( var i in disallowed ) {
		var index = k.indexOf( disallowed[ i ] );
		if ( -1 < index ) {
			k.splice( index, 1 );
		}
	}

	return k;
}

var keywords = parseKeywords( pkg.keywords );

var config = {
	pluginSlug: 'torro-forms',
	pluginName: 'Torro Forms',
	pluginURI: pkg.homepage,
	author: pkg.author.name,
	authorURI: pkg.author.url,
	description: pkg.description,
	version: pkg.version,
	license: 'GNU General Public License v3',
	licenseURI: 'http://www.gnu.org/licenses/gpl-3.0.html',
	tags: keywords.join( ', ' ),
	contributors: [ 'mahype', 'flixos90', 'awesome-ug' ].join( ', ' ),
	donateLink: false,
	minRequired: '4.7',
	testedUpTo: '4.7',
	translateURI: 'https://translate.wordpress.org/projects/wp-plugins/torro-forms',
	network: false
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
					( config.network ? 'Network:     true' + '\n' : '' ) +
					'Tags:        ' + config.tags;

// WP plugin header for readme.txt
var readmeheader =	'Plugin Name:       ' + config.pluginName + '\n' +
					'Plugin URI:        ' + config.pluginURI + '\n' +
					'Author:            ' + config.author + '\n' +
					'Author URI:        ' + config.authorURI + '\n' +
					'Contributors:      ' + config.contributors + '\n' +
					( config.donateLink ? 'Donate link:       ' + config.donateLink + '\n' : '' ) +
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

var rename = require( 'gulp-rename' );
var replace = require( 'gulp-replace' );
var banner = require( 'gulp-banner' );
var sass = require( 'gulp-sass' );
var csscomb = require( 'gulp-csscomb' );
var cleanCss = require( 'gulp-clean-css' );
var jshint = require( 'gulp-jshint' );
var jscs = require( 'gulp-jscs' );
var concat = require( 'gulp-concat' );
var uglify = require( 'gulp-uglify' );

var paths = {
	php: {
		files: [ './*.php', './src/**/*.php', './templates/**/*.php' ]
	},
	sass: {
		files: [ './assets/src/sass/*.scss' ],
		src: './assets/src/sass/',
		dst: './assets/dist/css/'
	},
	js: {
		files: [ './assets/src/js/*.js' ],
		builderFiles: [
			'./assets/src/js/admin-form-builder/app.js',
			'./assets/src/js/admin-form-builder/element-type.js',
			'./assets/src/js/admin-form-builder/element-types.js',
			'./assets/src/js/admin-form-builder/models/*.js',
			'./assets/src/js/admin-form-builder/collections/*.js',
			'./assets/src/js/admin-form-builder/factories/*.js',
			'./assets/src/js/admin-form-builder/views/*.js',
			'./assets/src/js/admin-form-builder/init.js',
		],
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
gulp.task( 'build', [ 'readme-replace' ], function() {
	gulp.start( 'header-replace' );
	gulp.start( 'default' );
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
		.pipe( banner( assetheader ) )
		.pipe( gulp.dest( paths.sass.dst ) )
		.pipe( cleanCss({
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
		.pipe( jshint() )
		.pipe( jshint.reporter( 'default' ) )
		.pipe( jscs() )
		.pipe( jscs.reporter() )
		.pipe( banner( assetheader ) )
		.pipe( gulp.dest( paths.js.dst ) )
		.pipe( uglify() )
		.pipe( banner( assetheader ) )
		.pipe( rename({
			extname: '.min.js'
		}) )
		.pipe( gulp.dest( paths.js.dst ) )
		.on( 'end', function() {
			gulp.src( paths.js.builderFiles )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( jscs() )
				.pipe( jscs.reporter() )
				.pipe( concat( 'admin-form-builder.js' ) )
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
