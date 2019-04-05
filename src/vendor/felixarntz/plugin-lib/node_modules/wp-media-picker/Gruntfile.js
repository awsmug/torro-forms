'use strict';
module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),
		banner: '/*!\n' +
				' * WP Media Picker - version <%= pkg.version %>\n' +
				' * \n' +
				' * <%= pkg.author.name %> <<%= pkg.author.email %>>\n' +
				' */',

		clean: {
			script: [
				'wp-media-picker.min.js'
			],
			stylesheet: [
				'wp-media-picker.min.css'
			]
		},

		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			script: {
				src: [
					'wp-media-picker.js'
				]
			}
		},

		uglify: {
			options: {
				preserveComments: 'some',
				report: 'min'
			},
			script: {
				src: 'wp-media-picker.js',
				dest: 'wp-media-picker.min.js'
			}
		},

		cssmin: {
			options: {
				compatibility: 'ie8',
				keepSpecialComments: '*',
				noAdvanced: true
			},
			stylesheet: {
				files: {
					'wp-media-picker.min.css': 'wp-media-picker.css'
				}
			}
		},

		usebanner: {
			options: {
				position: 'top',
				banner: '<%= banner %>'
			},
			script: {
				src: [
					'wp-media-picker.min.js'
				]
			},
			stylesheet: {
				src: [
					'wp-media-picker.min.css'
				]
			}
		}

 	});

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-banner');

	grunt.registerTask( 'script', [
		'clean:script',
		'jshint:script',
		'uglify:script',
		'usebanner:script'
	]);

	grunt.registerTask( 'stylesheet', [
		'clean:stylesheet',
		'cssmin:stylesheet',
		'usebanner:stylesheet'
	]);

	grunt.registerTask( 'default', [
		'script',
		'stylesheet'
	]);
};
