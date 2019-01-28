/* ---- THE FOLLOWING CONFIG SHOULD BE EDITED ---- */

const pkg = require( './package.json' );

function parseKeywords( keywords ) {
	// These keywords are useful for Packagist/NPM/Bower, but not for the WordPress plugin repository.
	const disallowed = [ 'wordpress', 'plugin' ];

	return keywords.filter( keyword => ! disallowed.includes( keyword ) );
}

const config = {
	pluginSlug: 'torro-forms',
	pluginName: 'Torro Forms',
	pluginURI: pkg.homepage,
	author: pkg.author.name,
	authorURI: pkg.author.url,
	description: pkg.description,
	version: pkg.version,
	license: 'GNU General Public License v2 (or later)',
	licenseURI: 'http://www.gnu.org/licenses/gpl-2.0.html',
	tags: parseKeywords( pkg.keywords ).join( ', ' ),
	contributors: [ 'mahype', 'flixos90', 'awesome-ug' ].join( ', ' ),
	donateLink: false,
	minRequired: '4.8',
	testedUpTo: '5.0.3',
	requiresPHP: '5.6',
	translateURI: 'https://translate.wordpress.org/projects/wp-plugins/torro-forms',
	network: false
};

/* ---- DO NOT EDIT BELOW THIS LINE ---- */

// WP plugin header for main plugin file
const pluginheader =' * Plugin Name: ' + config.pluginName + '\n' +
					' * Plugin URI:  ' + config.pluginURI + '\n' +
					' * Description: ' + config.description + '\n' +
					' * Version:     ' + config.version + '\n' +
					' * Author:      ' + config.author + '\n' +
					' * Author URI:  ' + config.authorURI + '\n' +
					' * License:     ' + config.license + '\n' +
					' * License URI: ' + config.licenseURI + '\n' +
					' * Text Domain: ' + config.pluginSlug + '\n' +
					( config.network ? ' * Network:     true' + '\n' : '' ) +
					' * Tags:        ' + config.tags;

// WP plugin header for readme.txt
const readmeheader ='Plugin Name:       ' + config.pluginName + '\n' +
					'Plugin URI:        ' + config.pluginURI + '\n' +
					'Author:            ' + config.author + '\n' +
					'Author URI:        ' + config.authorURI + '\n' +
					'Contributors:      ' + config.contributors + '\n' +
					( config.donateLink ? 'Donate link:       ' + config.donateLink + '\n' : '' ) +
					'Requires at least: ' + config.minRequired + '\n' +
					'Tested up to:      ' + config.testedUpTo + '\n' +
					( config.requiresPHP ? 'Requires PHP:      ' + config.requiresPHP + '\n' : '' ) +
					'Stable tag:        ' + config.version + '\n' +
					'Version:           ' + config.version + '\n' +
					'License:           ' + config.license + '\n' +
					'License URI:       ' + config.licenseURI + '\n' +
					'Tags:              ' + config.tags;

// header for minified assets
const assetheader =	'/*!\n' +
					' * ' + config.pluginName + ' Version ' + config.version + ' (' + config.pluginURI + ')\n' +
					' * Licensed under ' + config.license + ' (' + config.licenseURI + ')\n' +
					' */\n';


/* ---- REQUIRED DEPENDENCIES ---- */

const gulp = require( 'gulp' );

const rename = require( 'gulp-rename' );
const replace = require( 'gulp-replace' );
const banner = require( 'gulp-banner' );
const sass = require( 'gulp-sass' );
const csscomb = require( 'gulp-csscomb' );
const cleanCss = require( 'gulp-clean-css' );
const jshint = require( 'gulp-jshint' );
const jscs = require( 'gulp-jscs' );
const concat = require( 'gulp-concat' );
const uglify = require( 'gulp-uglify' );

const paths = {
	php: {
		files: [ './src/*.php', './src/src/**/*.php', './src/templates/**/*.php' ]
	},
	sass: {
		files: [ './src/assets/src/sass/*.scss' ],
		src: './src/assets/src/sass/',
		dst: './src/assets/dist/css/'
	},
	js: {
		files: [ './src/assets/src/js/*.js' ],
		builderFiles: [
			'./src/assets/src/js/admin-form-builder/app.js',
			'./src/assets/src/js/admin-form-builder/element-type.js',
			'./src/assets/src/js/admin-form-builder/element-types.js',
			'./src/assets/src/js/admin-form-builder/add-element/*.js',
			'./src/assets/src/js/admin-form-builder/base-model.js',
			'./src/assets/src/js/admin-form-builder/base-collection.js',
			'./src/assets/src/js/admin-form-builder/models/*.js',
			'./src/assets/src/js/admin-form-builder/collections/*.js',
			'./src/assets/src/js/admin-form-builder/views/*.js',
			'./src/assets/src/js/admin-form-builder/metabox-tabs.js',
			'./src/assets/src/js/admin-form-builder/unload.js',
			'./src/assets/src/js/admin-form-builder/init.js'
		],
		formFiles: [
			'./src/assets/src/js/form/app.js',
			'./src/assets/src/js/form/models/*.js',
			'./src/assets/src/js/form/collections/*.js',
			'./src/assets/src/js/form/init.js',
		],
		src: './src/assets/src/js/',
		dst: './src/assets/dist/js/'
	}
};

/* ---- MAIN TASKS ---- */

// general task (compile Sass and JavaScript and refresh POT file)
gulp.task( 'default', [ 'sass', 'js' ]);

// watch Sass and JavaScript files
gulp.task( 'watch', () => {
	gulp.watch( paths.sass.files, [ 'sass' ]);
	gulp.watch( paths.js.files, [ 'js' ]);
});

// build the plugin
gulp.task( 'build', [ 'readme-replace' ], () => {
	gulp.start( 'header-replace' );
	gulp.start( 'default' );
});

/* ---- SUB TASKS ---- */

// compile Sass
gulp.task( 'sass', done => {
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
gulp.task( 'js', done => {
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
		.on( 'end', () => {
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
			gulp.src( paths.js.formFiles )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( jscs() )
				.pipe( jscs.reporter() )
				.pipe( concat( 'form.js' ) )
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
gulp.task( 'header-replace', done => {
	gulp.src( './src/' + config.pluginSlug + '.php' )
		.pipe( replace( /(?:\s\*\s@wordpress-plugin\s(?:[^*]|(?:\*+[^*\/]))*\*+\/)/, ' * @wordpress-plugin\n' + pluginheader + '\n */' ) )
		.pipe( gulp.dest( './src/' ) )
		.on( 'end', done );
});

// replace the plugin header in readme.txt
gulp.task( 'readme-replace', done => {
	gulp.src( './src/readme.txt' )
		.pipe( replace( /\=\=\= (.+) \=\=\=([\s\S]+)\=\= Description \=\=/m, '=== ' + config.pluginName + ' ===\n\n' + readmeheader + '\n\n' + config.description + '\n\n== Description ==' ) )
		.pipe( gulp.dest( './src/' ) )
		.on( 'end', done );
});
