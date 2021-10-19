'use strict';
module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),
		banner: '/*!\n' +
				' * WP Map Picker -  version <%= pkg.version %>\n' +
				' * \n' +
				' * <%= pkg.author.name %> <<%= pkg.author.email %>>\n' +
				' */',

		clean: {
			script: [
				'wp-map-picker.min.js'
			],
			stylesheet: [
				'wp-map-picker.min.css'
			]
		},

		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			script: {
				src: [
					'wp-map-picker.js'
				]
			}
		},

		uglify: {
			options: {
				preserveComments: 'some',
				report: 'min'
			},
			script: {
				src: 'wp-map-picker.js',
				dest: 'wp-map-picker.min.js'
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
					'wp-map-picker.min.css': 'wp-map-picker.css'
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
					'wp-map-picker.min.js'
				]
			},
			stylesheet: {
				src: [
					'wp-map-picker.min.css'
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
